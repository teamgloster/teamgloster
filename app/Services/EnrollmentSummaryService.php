<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\YearLevel;
use Illuminate\Support\Collection;

class EnrollmentSummaryService
{
    public const PASSING_GRADE = 75;

    /**
     * @return list<string>
     */
    public function availableSchoolYears(?string $currentSchoolYear = null): array
    {
        $years = Enrollment::query()
            ->select('school_year')
            ->whereNotNull('school_year')
            ->distinct()
            ->orderByDesc('school_year')
            ->pluck('school_year')
            ->filter()
            ->values()
            ->all();

        if ($currentSchoolYear && ! in_array($currentSchoolYear, $years, true)) {
            array_unshift($years, $currentSchoolYear);
        }

        return $years;
    }

    /**
     * @return array<string, mixed>
     */
    public function summarize(string $schoolYear): array
    {
        $yearLevels = YearLevel::query()->get()->keyBy('id');
        $subjectsByYearLevel = Subject::query()
            ->active()
            ->get()
            ->groupBy('year_level_id');

        $enrollments = Enrollment::query()
            ->where('school_year', $schoolYear)
            ->with(['user', 'yearLevel'])
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->values();

        $nextSchoolYear = $this->nextSchoolYear($schoolYear);
        $nextByUser = Enrollment::query()
            ->where('school_year', $nextSchoolYear)
            ->whereIn('status', ['enrolled', 'approved'])
            ->with('yearLevel')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $gradesByStudent = Grade::query()
            ->where('school_year', $schoolYear)
            ->whereNotNull('final_grade')
            ->get()
            ->groupBy('student_id');

        $rows = [];
        foreach ($yearLevels->sortBy(fn (YearLevel $level) => $level->rank) as $yearLevel) {
            $rows[$yearLevel->id] = $this->emptyYearLevelRow($yearLevel);
        }

        $totals = $this->emptyTotals();

        foreach ($enrollments as $enrollment) {
            $yearLevel = $enrollment->yearLevel;
            $row = $yearLevel ? ($rows[$yearLevel->id] ?? $this->emptyYearLevelRow($yearLevel)) : null;
            $outcome = $this->outcome(
                $enrollment,
                $yearLevel,
                $nextByUser->get($enrollment->user_id),
                $subjectsByYearLevel->get($yearLevel?->id, collect()),
                $gradesByStudent->get($enrollment->user_id, collect()),
            );

            $this->tally($totals, $outcome);
            if ($row !== null) {
                $this->tally($row, $outcome);
                $rows[$yearLevel->id] = $row;
            }
        }

        $this->attachRates($totals);
        foreach ($rows as $id => $row) {
            $this->attachRates($row);
            $rows[$id] = $row;
        }

        return [
            'school_year' => $schoolYear,
            'next_school_year' => $nextSchoolYear,
            'has_next_year_records' => $nextByUser->isNotEmpty(),
            'totals' => $totals,
            'by_year_level' => array_values($rows),
        ];
    }

    /**
     * @return array<string, int|float|null>
     */
    private function emptyTotals(): array
    {
        return [
            'enrolled' => 0,
            'dropped' => 0,
            'rejected' => 0,
            'pending' => 0,
            'promoted' => 0,
            'retained' => 0,
            'incomplete' => 0,
            'failed' => 0,
            'completed' => 0,
            'graduated' => 0,
            'promotion_base' => 0,
            'completion_base' => 0,
            'graduation_base' => 0,
            'cohort' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyYearLevelRow(YearLevel $yearLevel): array
    {
        return [
            'year_level_id' => $yearLevel->id,
            'name' => $yearLevel->name,
            'code' => $yearLevel->code,
            'rank' => $yearLevel->rank,
            ...$this->emptyTotals(),
        ];
    }

    /**
     * @param  Collection<int, Subject>  $subjects
     * @param  Collection<int, Grade>  $grades
     * @return array<string, bool|string>
     */
    private function outcome(
        Enrollment $enrollment,
        ?YearLevel $yearLevel,
        ?Enrollment $nextEnrollment,
        Collection $subjects,
        Collection $grades,
    ): array {
        $status = $enrollment->status;
        $rank = $yearLevel?->rank ?? 0;
        $isGrade10 = $rank === 10;
        $isGrade12 = $rank === 12;
        $canPromote = $rank >= 7 && $rank < 12;

        $result = [
            'enrolled' => in_array($status, ['enrolled', 'approved'], true),
            'dropped' => $status === 'dropped',
            'rejected' => $status === 'rejected',
            'pending' => $status === 'pending',
            'promoted' => false,
            'retained' => false,
            'incomplete' => false,
            'failed' => false,
            'completed' => false,
            'graduated' => false,
            'promotion_base' => false,
            'completion_base' => $isGrade10 && in_array($status, ['enrolled', 'approved'], true),
            'graduation_base' => $isGrade12 && in_array($status, ['enrolled', 'approved'], true),
        ];

        if (! $result['enrolled']) {
            return $result;
        }

        $evaluation = $this->evaluateGrades($subjects, $grades);
        $nextRank = $nextEnrollment?->yearLevel?->rank;

        if ($nextRank !== null) {
            if ($nextRank > $rank) {
                $result['promoted'] = $canPromote;
                $result['completed'] = $isGrade10;
                $result['graduated'] = $isGrade12 && $nextRank > 12;
            } elseif ($nextRank === $rank) {
                $result['retained'] = true;
            }
        } elseif ($evaluation['incomplete']) {
            $result['incomplete'] = true;
        } elseif ($evaluation['passed']) {
            if ($isGrade12) {
                $result['graduated'] = true;
            } elseif ($isGrade10) {
                $result['completed'] = true;
                $result['promoted'] = true;
            } elseif ($canPromote) {
                $result['promoted'] = true;
            }
        } else {
            $result['failed'] = true;
            $result['retained'] = true;
        }

        $result['promotion_base'] = $canPromote;

        return $result;
    }

    /**
     * @param  Collection<int, Subject>  $subjects
     * @param  Collection<int, Grade>  $grades
     * @return array{passed: bool, incomplete: bool}
     */
    private function evaluateGrades(Collection $subjects, Collection $grades): array
    {
        if ($subjects->isEmpty()) {
            if ($grades->isEmpty()) {
                return ['passed' => false, 'incomplete' => true];
            }

            $failed = $grades->contains(
                fn (Grade $grade) => (float) $grade->final_grade < self::PASSING_GRADE
            );

            return ['passed' => ! $failed, 'incomplete' => false];
        }

        $bySubject = $grades->keyBy('subject_id');
        $missing = false;
        $failed = false;

        foreach ($subjects as $subject) {
            $grade = $bySubject->get($subject->id);
            if ($grade?->final_grade === null) {
                $missing = true;

                continue;
            }

            if ((float) $grade->final_grade < self::PASSING_GRADE) {
                $failed = true;
            }
        }

        return [
            'passed' => ! $missing && ! $failed,
            'incomplete' => $missing,
        ];
    }

    /**
     * @param  array<string, int|float|null>  $bucket
     * @param  array<string, bool|string>  $outcome
     */
    private function tally(array &$bucket, array $outcome): void
    {
        foreach ([
            'enrolled',
            'dropped',
            'rejected',
            'pending',
            'promoted',
            'retained',
            'incomplete',
            'failed',
            'completed',
            'graduated',
        ] as $key) {
            if ($outcome[$key]) {
                $bucket[$key]++;
            }
        }

        if ($outcome['enrolled'] || $outcome['dropped']) {
            $bucket['cohort']++;
        }

        if ($outcome['promotion_base']) {
            $bucket['promotion_base']++;
        }

        if ($outcome['completion_base']) {
            $bucket['completion_base']++;
        }

        if ($outcome['graduation_base']) {
            $bucket['graduation_base']++;
        }
    }

    /**
     * @param  array<string, int|float|null>  $bucket
     */
    private function attachRates(array &$bucket): void
    {
        $bucket['dropout_rate'] = $this->rate($bucket['dropped'], $bucket['cohort']);
        $bucket['promotion_rate'] = $this->rate($bucket['promoted'], $bucket['promotion_base']);
        $bucket['completion_rate'] = $this->rate($bucket['completed'], $bucket['completion_base']);
        $bucket['graduation_rate'] = $this->rate($bucket['graduated'], $bucket['graduation_base']);
    }

    private function rate(int $count, int $base): ?float
    {
        if ($base <= 0) {
            return null;
        }

        return round(($count / $base) * 100, 2);
    }

    public function nextSchoolYear(string $schoolYear): string
    {
        if (! preg_match('/^(\d{4})-(\d{4})$/', $schoolYear, $matches)) {
            return $schoolYear;
        }

        return ((int) $matches[1] + 1).'-'.((int) $matches[2] + 1);
    }
}
