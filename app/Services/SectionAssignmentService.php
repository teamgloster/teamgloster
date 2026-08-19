<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\YearLevel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SectionAssignmentService
{
    /**
     * Assign unsectioned approved/enrolled students using the selected method.
     *
     * @return array{assigned: int, message: string, warnings: array<int, string>}
     */
    public function assign(string $method, ?int $yearLevelId, string $schoolYear): array
    {
        $query = Enrollment::query()
            ->where('school_year', $schoolYear)
            ->whereIn('status', ['approved', 'enrolled'])
            ->whereNull('section_id')
            ->with(['user', 'yearLevel']);

        if ($yearLevelId) {
            $query->where('year_level_id', $yearLevelId);
        }

        $enrollments = $query->get();

        if ($enrollments->isEmpty()) {
            return [
                'assigned' => 0,
                'message' => 'No approved students are waiting for a section.',
                'warnings' => [],
            ];
        }

        $assignedCount = 0;
        $warnings = [];

        DB::transaction(function () use ($enrollments, $method, $schoolYear, &$assignedCount, &$warnings) {
            foreach ($enrollments->groupBy('year_level_id') as $yearLevelId => $yearEnrollments) {
                $slots = $this->sectionSlots((int) $yearLevelId, $schoolYear);

                if ($slots->isEmpty()) {
                    $yearLevel = YearLevel::find($yearLevelId);
                    $warnings[] = 'No available section slots for '.($yearLevel?->name ?? 'this year level');
                    continue;
                }

                $assignedCount += match ($method) {
                    'gwa' => $this->assignByGwa($yearEnrollments, $slots),
                    'mixed' => $this->assignMixed($yearEnrollments, $slots),
                    default => $this->assignShuffle($yearEnrollments, $slots),
                };

                if ($this->unplacedCount($yearEnrollments) > 0) {
                    $yearLevel = YearLevel::find($yearLevelId);
                    $warnings[] = 'Not all students could be assigned for '.($yearLevel?->name ?? 'this year level').' — sections are full';
                }
            }
        });

        $label = match ($method) {
            'gwa' => 'GWA ranking',
            'mixed' => 'mixed high and low GWA',
            default => 'shuffle',
        };

        return [
            'assigned' => $assignedCount,
            'message' => "Assigned {$assignedCount} student(s) to sections using {$label}.",
            'warnings' => $warnings,
        ];
    }

    /**
     * Highest GWA students are grouped together by rank.
     */
    private function assignByGwa(Collection $enrollments, Collection $slots): int
    {
        $sorted = $this->sortedByGwa($enrollments)->values();
        $assigned = 0;
        $index = 0;
        $total = $sorted->count();

        foreach ($slots as $slotIndex => $slot) {
            $remainingStudents = $total - $index;
            $remainingSections = $slots->count() - $slotIndex;

            if ($remainingStudents <= 0 || $slot->remaining <= 0) {
                continue;
            }

            $share = (int) ceil($remainingStudents / max(1, $remainingSections));
            $take = min($share, $slot->remaining);

            for ($n = 0; $n < $take && $index < $total; $n++) {
                $this->place($sorted[$index], $slot);
                $index++;
                $assigned++;
            }
        }

        return $assigned;
    }

    /**
     * Each section receives a mix of high-GWA and low-GWA students.
     */
    private function assignMixed(Collection $enrollments, Collection $slots): int
    {
        $sorted = $this->sortedByGwa($enrollments)->values()->all();
        $high = 0;
        $low = count($sorted) - 1;
        $assigned = 0;

        while ($high <= $low) {
            $placedThisPass = false;

            foreach ($slots as $slot) {
                if ($high > $low) {
                    break;
                }
                if ($slot->remaining <= 0) {
                    continue;
                }

                $this->place($sorted[$high], $slot);
                $high++;
                $assigned++;
                $placedThisPass = true;
            }

            foreach ($slots as $slot) {
                if ($high > $low) {
                    break;
                }
                if ($slot->remaining <= 0) {
                    continue;
                }

                $this->place($sorted[$low], $slot);
                $low--;
                $assigned++;
                $placedThisPass = true;
            }

            if (! $placedThisPass) {
                break;
            }
        }

        return $assigned;
    }

    /**
     * Students are assigned at random, spread evenly across sections.
     */
    private function assignShuffle(Collection $enrollments, Collection $slots): int
    {
        $shuffled = $enrollments->shuffle()->values();
        $assigned = 0;
        $sectionIndex = 0;
        $sectionCount = $slots->count();

        foreach ($shuffled as $enrollment) {
            $attempts = 0;

            while ($attempts < $sectionCount) {
                $slot = $slots[$sectionIndex];
                $sectionIndex = ($sectionIndex + 1) % $sectionCount;

                if ($slot->remaining > 0) {
                    $this->place($enrollment, $slot);
                    $assigned++;
                    break;
                }

                $attempts++;
            }

            if ($attempts >= $sectionCount) {
                break;
            }
        }

        return $assigned;
    }

    private function sectionSlots(int $yearLevelId, string $schoolYear): Collection
    {
        return Section::query()
            ->where('year_level_id', $yearLevelId)
            ->where('school_year', $schoolYear)
            ->where('is_active', true)
            ->withCount(['enrollments as occupied_count' => function ($query) {
                $query->whereIn('status', ['approved', 'enrolled']);
            }])
            ->orderBy('name')
            ->get()
            ->map(function (Section $section) {
                $capacity = $section->capacity ?? 40;

                return (object) [
                    'section' => $section,
                    'remaining' => max(0, $capacity - (int) $section->occupied_count),
                ];
            })
            ->filter(fn (object $slot) => $slot->remaining > 0)
            ->values();
    }

    private function sortedByGwa(Collection $enrollments): Collection
    {
        return $enrollments->sortBy(function (Enrollment $enrollment) {
            $gwa = number_format((float) ($enrollment->previous_gwa ?? 0), 2, '.', '');
            $last = strtolower($enrollment->user?->last_name ?? '');
            $first = strtolower($enrollment->user?->first_name ?? '');

            return sprintf('%010.2f-%s-%s', 100 - (float) $gwa, $last, $first);
        });
    }

    private function place(Enrollment $enrollment, object $slot): void
    {
        $enrollment->update([
            'section_id' => $slot->section->id,
        ]);

        $slot->remaining--;
    }

    private function unplacedCount(Collection $enrollments): int
    {
        return $enrollments->filter(fn (Enrollment $enrollment) => $enrollment->section_id === null)->count();
    }
}
