<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;
use App\Models\YearLevel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SectionAssignmentService
{
    private bool $persist = true;

    /**
     * @var list<array<string, mixed>>
     */
    private array $proposed = [];

    /**
     * Assign unsectioned approved/enrolled students using the selected method.
     *
     * @return array{assigned: int, message: string, warnings: array<int, string>}
     */
    public function assign(string $method, ?int $yearLevelId, string $schoolYear, string $gwaSource = 'admission'): array
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

        if ($gwaSource === 'teacher_grades') {
            $this->applyTeacherGradeGwa($enrollments, $schoolYear);
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

        $sourceLabel = $gwaSource === 'teacher_grades'
            ? 'teacher-entered grades'
            : 'admission GWA';

        return [
            'assigned' => $assignedCount,
            'message' => "Assigned {$assignedCount} student(s) to sections using {$label} from {$sourceLabel}.",
            'warnings' => $warnings,
        ];
    }

    /**
     * Reassign already-sectioned students using teacher-entered grades.
     *
     * @return array{assigned: int, message: string, warnings: array<int, string>, preview: list<array<string, mixed>>}
     */
    public function reshuffleByTeacherGrades(string $method, int $yearLevelId, string $schoolYear, bool $persist = true): array
    {
        $enrollments = Enrollment::query()
            ->where('school_year', $schoolYear)
            ->where('year_level_id', $yearLevelId)
            ->whereIn('status', ['approved', 'enrolled'])
            ->with(['user', 'yearLevel', 'section'])
            ->get();

        if ($enrollments->isEmpty()) {
            return [
                'assigned' => 0,
                'message' => 'No enrolled or approved students were found for this year level.',
                'warnings' => [],
                'preview' => [],
            ];
        }

        $this->applyTeacherGradeGwa($enrollments, $schoolYear);

        $missingGrades = $enrollments->filter(
            fn (Enrollment $enrollment) => $enrollment->getAttribute('assignment_gwa') === null
        )->count();

        $slots = $this->sectionSlots($yearLevelId, $schoolYear, true);

        if ($slots->isEmpty()) {
            $yearLevel = YearLevel::find($yearLevelId);

            return [
                'assigned' => 0,
                'message' => 'No active sections are available for '.($yearLevel?->name ?? 'this year level').'.',
                'warnings' => [],
                'preview' => [],
            ];
        }

        $this->persist = $persist;
        $this->proposed = [];
        $assignedCount = 0;
        $warnings = [];

        $run = function () use ($enrollments, $method, $slots, &$assignedCount) {
            foreach ($enrollments as $enrollment) {
                if ($this->persist) {
                    $enrollment->update(['section_id' => null]);
                } else {
                    $enrollment->section_id = null;
                }
            }

            $assignedCount = match ($method) {
                'gwa' => $this->assignByGwa($enrollments, $slots),
                'mixed' => $this->assignMixed($enrollments, $slots),
                default => $this->assignShuffle($enrollments, $slots),
            };
        };

        if ($persist) {
            DB::transaction($run);
        } else {
            $run();
        }

        if ($this->unplacedCount($enrollments) > 0) {
            $yearLevel = YearLevel::find($yearLevelId);
            $warnings[] = 'Not all students could be assigned for '.($yearLevel?->name ?? 'this year level').' — sections are full';
        }

        if ($missingGrades > 0) {
            $warnings[] = $missingGrades.' student(s) have no teacher-entered final grades; admission GWA was used when available.';
        }

        $label = match ($method) {
            'gwa' => 'GWA ranking',
            'mixed' => 'mixed high and low GWA',
            default => 'shuffle',
        };

        $this->persist = true;

        return [
            'assigned' => $assignedCount,
            'message' => $persist
                ? "Reshuffled {$assignedCount} student(s) using {$label} from teacher-entered grades."
                : "Preview: {$assignedCount} student(s) would be reshuffled using {$label} from teacher-entered grades.",
            'warnings' => $warnings,
            'preview' => $this->proposed,
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

    private function sectionSlots(int $yearLevelId, string $schoolYear, bool $emptyFirst = false): Collection
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
            ->map(function (Section $section) use ($emptyFirst) {
                $capacity = $section->capacity ?? 40;
                $occupied = $emptyFirst ? 0 : (int) $section->occupied_count;

                return (object) [
                    'section' => $section,
                    'remaining' => max(0, $capacity - $occupied),
                ];
            })
            ->filter(fn (object $slot) => $slot->remaining > 0)
            ->values();
    }

    private function applyTeacherGradeGwa(Collection $enrollments, string $schoolYear): void
    {
        $averages = Grade::query()
            ->whereIn('student_id', $enrollments->pluck('user_id'))
            ->where('school_year', $schoolYear)
            ->whereNotNull('final_grade')
            ->selectRaw('student_id, ROUND(AVG(final_grade), 2) as gwa')
            ->groupBy('student_id')
            ->pluck('gwa', 'student_id');

        foreach ($enrollments as $enrollment) {
            $fromGrades = $averages->get($enrollment->user_id);
            $enrollment->setAttribute(
                'assignment_gwa',
                $fromGrades !== null
                    ? (float) $fromGrades
                    : ($enrollment->previous_gwa !== null ? (float) $enrollment->previous_gwa : null)
            );
            $enrollment->syncOriginalAttribute('assignment_gwa');
        }
    }

    private function assignmentGwa(Enrollment $enrollment): float
    {
        $gwa = $enrollment->getAttribute('assignment_gwa') ?? $enrollment->previous_gwa ?? 0;

        return (float) $gwa;
    }

    private function sortedByGwa(Collection $enrollments): Collection
    {
        return $enrollments->sortBy(function (Enrollment $enrollment) {
            $gwa = $this->assignmentGwa($enrollment);
            $last = strtolower($enrollment->user?->last_name ?? '');
            $first = strtolower($enrollment->user?->first_name ?? '');

            return sprintf('%010.2f-%s-%s', 100 - $gwa, $last, $first);
        });
    }

    private function place(Enrollment $enrollment, object $slot): void
    {
        if ($this->persist) {
            $enrollment->update([
                'section_id' => $slot->section->id,
            ]);
        } else {
            $enrollment->section_id = $slot->section->id;
        }

        $this->proposed[] = [
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->user_id,
            'name' => trim(($enrollment->user?->last_name ?? '').', '.($enrollment->user?->first_name ?? '')),
            'gwa' => $this->assignmentGwa($enrollment),
            'section_id' => $slot->section->id,
            'section' => $slot->section->name,
        ];

        $slot->remaining--;
    }

    private function unplacedCount(Collection $enrollments): int
    {
        return $enrollments->filter(fn (Enrollment $enrollment) => $enrollment->section_id === null)->count();
    }
}
