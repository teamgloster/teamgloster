<?php

namespace App\Services;

use App\Exceptions\StudentPromotionException;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentPromotionService
{
    public const PASSING_GRADE = 75;

    /**
     * @param  Collection<int, User>|iterable<User>  $students
     */
    public function attachSummaries(iterable $students, ?string $schoolYear = null): void
    {
        $schoolYear ??= SchoolSetting::currentSchoolYear();
        $studentIds = collect($students)->pluck('id')->filter()->unique()->values();

        if ($studentIds->isEmpty()) {
            return;
        }

        $yearLevels = YearLevel::query()->get()->keyBy('id');
        $subjectsByYearLevel = Subject::query()
            ->active()
            ->get()
            ->groupBy('year_level_id');

        $enrollmentsByUser = Enrollment::query()
            ->whereIn('user_id', $studentIds)
            ->with('yearLevel')
            ->orderByDesc('school_year')
            ->orderByDesc('id')
            ->get()
            ->groupBy('user_id');

        $gradesByStudent = Grade::query()
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        foreach ($students as $student) {
            $student->setAttribute(
                'promotion',
                $this->buildSummary(
                    $student,
                    $schoolYear,
                    $yearLevels,
                    $enrollmentsByUser->get($student->id, collect()),
                    $subjectsByYearLevel,
                    $gradesByStudent->get($student->id, collect()),
                ),
            );
        }
    }

    public function summary(User $student, ?string $schoolYear = null): array
    {
        $schoolYear ??= SchoolSetting::currentSchoolYear();
        $yearLevels = YearLevel::query()->get()->keyBy('id');
        $subjectsByYearLevel = Subject::query()
            ->active()
            ->get()
            ->groupBy('year_level_id');

        $enrollments = $student->enrollments()
            ->with('yearLevel')
            ->orderByDesc('school_year')
            ->orderByDesc('id')
            ->get();

        $grades = Grade::query()
            ->where('student_id', $student->id)
            ->get();

        return $this->buildSummary(
            $student,
            $schoolYear,
            $yearLevels,
            $enrollments,
            $subjectsByYearLevel,
            $grades,
        );
    }

    public function promote(User $student, ?string $remarks = null): Enrollment
    {
        $summary = $this->summary($student);

        if (! $summary['can_promote']) {
            throw new StudentPromotionException($summary['message']);
        }

        $target = YearLevel::query()->find($summary['next_year_level']['id'] ?? null);
        if (! $target) {
            throw new StudentPromotionException('There is no next year level available for promotion.');
        }

        return $this->applyYearLevel($student, $target, 'promote', $summary, $remarks);
    }

    public function changeYearLevel(User $student, int $yearLevelId, ?string $remarks = null): Enrollment
    {
        $summary = $this->summary($student);
        $target = YearLevel::query()->find($yearLevelId);

        if (! $target) {
            throw new StudentPromotionException('The selected year level was not found.');
        }

        if (! in_array((int) $target->id, $summary['allowed_year_level_ids'], true)) {
            throw new StudentPromotionException($this->changeBlockedMessage($summary, $target));
        }

        $currentId = $summary['current_year_level']['id'] ?? null;
        if ($currentId !== null && (int) $currentId === (int) $target->id) {
            throw new StudentPromotionException('This student is already in '.$target->name.'.');
        }

        $action = $this->changeAction($summary, $target);

        return $this->applyYearLevel($student, $target, $action, $summary, $remarks);
    }

    /**
     * @param  Collection<int, User>|iterable<User>  $students
     * @return array{promoted: int, skipped: int, message: string}
     */
    public function promoteMany(iterable $students): array
    {
        $promoted = 0;
        $skipped = 0;

        DB::transaction(function () use ($students, &$promoted, &$skipped) {
            foreach ($students as $student) {
                try {
                    $this->promote($student);
                    $promoted++;
                } catch (StudentPromotionException) {
                    $skipped++;
                }
            }
        });

        $message = "Promoted {$promoted} student(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} student(s) were skipped because they have not passed or cannot be promoted.";
        }

        return [
            'promoted' => $promoted,
            'skipped' => $skipped,
            'message' => $message,
        ];
    }

    /**
     * @param  Collection<int, YearLevel>  $yearLevels
     * @param  Collection<int, Enrollment>  $enrollments
     * @param  Collection<int|string, Collection<int, Subject>>  $subjectsByYearLevel
     * @param  Collection<int, Grade>  $grades
     */
    private function buildSummary(
        User $student,
        string $schoolYear,
        Collection $yearLevels,
        Collection $enrollments,
        Collection $subjectsByYearLevel,
        Collection $grades,
    ): array {
        $currentEnrollment = $enrollments->first(
            fn (Enrollment $enrollment) => $enrollment->school_year === $schoolYear
        );

        $sourceEnrollment = $enrollments->first(function (Enrollment $enrollment) use ($schoolYear) {
            if ($enrollment->school_year === $schoolYear) {
                return in_array($enrollment->status, ['enrolled', 'approved', 'pending'], true);
            }

            return false;
        });

        if (! $sourceEnrollment) {
            $sourceEnrollment = $enrollments->first(
                fn (Enrollment $enrollment) => in_array($enrollment->status, ['enrolled', 'approved'], true)
            );
        }

        $sourceYearLevel = $sourceEnrollment?->yearLevel
            ?? $yearLevels->first(function (YearLevel $level) use ($student) {
                return $level->name === $student->year_level_applying
                    || $level->code === $student->year_level_applying;
            });

        $result = $this->evaluateGrades(
            $sourceYearLevel,
            $sourceEnrollment?->school_year ?? $schoolYear,
            $subjectsByYearLevel,
            $grades,
        );

        $nextYearLevel = $this->nextFrom($yearLevels, $sourceYearLevel);
        $currentYearLevel = $currentEnrollment?->yearLevel;
        $passed = $result['passed'];
        $hasSource = $sourceYearLevel !== null;

        $previousCompleted = $enrollments->first(
            fn (Enrollment $enrollment) => $enrollment->school_year !== $schoolYear
                && in_array($enrollment->status, ['enrolled', 'approved'], true)
        );
        $previousNext = $this->nextFrom($yearLevels, $previousCompleted?->yearLevel);

        $allowedYearLevelIds = $yearLevels
            ->filter(function (YearLevel $level) use ($sourceYearLevel, $passed) {
                if (! $level->is_active) {
                    return false;
                }

                if (! $sourceYearLevel) {
                    return true;
                }

                if ($level->rank < $sourceYearLevel->rank) {
                    return false;
                }

                if ($level->rank > $sourceYearLevel->rank && ! $passed) {
                    return false;
                }

                return true;
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $alreadyAtNext = $currentYearLevel
            && $nextYearLevel
            && $currentYearLevel->id === $nextYearLevel->id;

        if (
            ! $alreadyAtNext
            && $currentYearLevel
            && $previousNext
            && $currentYearLevel->id === $previousNext->id
            && $result['incomplete']
        ) {
            $alreadyAtNext = true;
        }

        $isHighestLevel = $sourceYearLevel && ! $nextYearLevel;
        $canPromote = $passed && $nextYearLevel && ! $alreadyAtNext;

        $status = $this->resolveStatus(
            hasSource: $hasSource,
            passed: $passed,
            incomplete: $result['incomplete'],
            alreadyAtNext: (bool) $alreadyAtNext,
            isHighestLevel: (bool) $isHighestLevel,
            canPromote: $canPromote,
        );

        return [
            'status' => $status,
            'passed' => $passed,
            'incomplete' => $result['incomplete'],
            'can_promote' => $canPromote,
            'gwa' => $result['gwa'],
            'passing_grade' => self::PASSING_GRADE,
            'source_school_year' => $sourceEnrollment?->school_year,
            'source_year_level' => $this->yearLevelPayload($sourceYearLevel),
            'current_year_level' => $this->yearLevelPayload($currentYearLevel),
            'next_year_level' => $this->yearLevelPayload($nextYearLevel),
            'allowed_year_level_ids' => $allowedYearLevelIds,
            'graded_subjects' => $result['graded_subjects'],
            'required_subjects' => $result['required_subjects'],
            'failed_subjects' => $result['failed_subjects'],
            'missing_subjects' => $result['missing_subjects'],
            'message' => $this->statusMessage(
                $status,
                $sourceYearLevel,
                $nextYearLevel,
                $result,
            ),
        ];
    }

    /**
     * @param  Collection<int|string, Collection<int, Subject>>  $subjectsByYearLevel
     * @param  Collection<int, Grade>  $grades
     * @return array{
     *     passed: bool,
     *     incomplete: bool,
     *     gwa: float|null,
     *     graded_subjects: int,
     *     required_subjects: int,
     *     failed_subjects: array<int, string>,
     *     missing_subjects: array<int, string>
     * }
     */
    private function evaluateGrades(
        ?YearLevel $yearLevel,
        string $schoolYear,
        Collection $subjectsByYearLevel,
        Collection $grades,
    ): array {
        $empty = [
            'passed' => false,
            'incomplete' => true,
            'gwa' => null,
            'graded_subjects' => 0,
            'required_subjects' => 0,
            'failed_subjects' => [],
            'missing_subjects' => [],
        ];

        if (! $yearLevel) {
            return $empty;
        }

        $yearGrades = $grades
            ->where('school_year', $schoolYear)
            ->keyBy('subject_id');

        $subjects = $subjectsByYearLevel->get($yearLevel->id, collect());

        if ($subjects->isEmpty()) {
            $finals = $yearGrades
                ->pluck('final_grade')
                ->filter(fn ($grade) => $grade !== null);

            if ($finals->isEmpty()) {
                return $empty;
            }

            $failed = $yearGrades
                ->filter(fn (Grade $grade) => $grade->final_grade !== null && (float) $grade->final_grade < self::PASSING_GRADE)
                ->count();

            return [
                'passed' => $failed === 0,
                'incomplete' => false,
                'gwa' => round((float) $finals->avg(), 2),
                'graded_subjects' => $finals->count(),
                'required_subjects' => $finals->count(),
                'failed_subjects' => [],
                'missing_subjects' => [],
            ];
        }

        $failedSubjects = [];
        $missingSubjects = [];
        $finals = [];

        foreach ($subjects as $subject) {
            $grade = $yearGrades->get($subject->id);
            $final = $grade?->final_grade;

            if ($final === null) {
                $missingSubjects[] = $subject->name;

                continue;
            }

            $finals[] = (float) $final;
            if ((float) $final < self::PASSING_GRADE) {
                $failedSubjects[] = $subject->name;
            }
        }

        $incomplete = $missingSubjects !== [];
        $passed = ! $incomplete && $failedSubjects === [];

        return [
            'passed' => $passed,
            'incomplete' => $incomplete,
            'gwa' => $finals === [] ? null : round(array_sum($finals) / count($finals), 2),
            'graded_subjects' => count($finals),
            'required_subjects' => $subjects->count(),
            'failed_subjects' => $failedSubjects,
            'missing_subjects' => $missingSubjects,
        ];
    }

    private function nextFrom(Collection $yearLevels, ?YearLevel $current): ?YearLevel
    {
        if (! $current) {
            return null;
        }

        return $yearLevels
            ->filter(fn (YearLevel $level) => $level->is_active && $level->rank > $current->rank)
            ->sortBy(fn (YearLevel $level) => $level->rank)
            ->first();
    }

    private function applyYearLevel(
        User $student,
        YearLevel $target,
        string $action,
        array $summary,
        ?string $remarks,
    ): Enrollment {
        if ($student->role !== 'student') {
            throw new StudentPromotionException('Only students can be promoted or reassigned.');
        }

        $schoolYear = SchoolSetting::currentSchoolYear();
        $sourceName = $summary['source_year_level']['name'] ?? 'the previous year level';
        $sourceSchoolYear = $summary['source_school_year'] ?? $schoolYear;

        return DB::transaction(function () use ($student, $target, $action, $summary, $remarks, $schoolYear, $sourceName, $sourceSchoolYear) {
            $enrollment = Enrollment::query()
                ->where('user_id', $student->id)
                ->where('school_year', $schoolYear)
                ->orderByDesc('id')
                ->first();

            $yearLevelChanged = ! $enrollment || (int) $enrollment->year_level_id !== (int) $target->id;
            $note = $this->buildRemarks($action, $sourceName, $sourceSchoolYear, $target->name, $remarks);

            $payload = [
                'year_level_id' => $target->id,
                'previous_gwa' => $summary['gwa'] ?? $enrollment?->previous_gwa ?? $student->previous_gwa,
                'remarks' => $this->appendRemarks($enrollment?->remarks, $note),
            ];

            if ($yearLevelChanged) {
                $payload['section_id'] = null;
            }

            if ($enrollment) {
                if (in_array($enrollment->status, ['rejected', 'dropped'], true)) {
                    $payload['status'] = 'approved';
                    $payload['approved_by'] = auth()->id();
                }

                if ($enrollment->enrollment_type === 'new' && $action !== 'place') {
                    $payload['enrollment_type'] = 'old';
                }

                $enrollment->update($payload);
            } else {
                if (! $student->isAdmissionApproved()) {
                    throw new StudentPromotionException('This student must have an approved admission before a year level can be assigned.');
                }

                $enrollment = Enrollment::create([
                    ...$payload,
                    'user_id' => $student->id,
                    'school_year' => $schoolYear,
                    'semester' => 'first',
                    'status' => 'approved',
                    'enrollment_type' => 'old',
                    'approved_by' => auth()->id(),
                ]);
            }

            $student->update([
                'year_level_applying' => $target->name,
                'previous_gwa' => $summary['gwa'] ?? $student->previous_gwa,
            ]);

            return $enrollment->fresh(['yearLevel', 'section']);
        });
    }

    private function changeAction(array $summary, YearLevel $target): string
    {
        $sourceRank = $summary['source_year_level']['rank'] ?? null;

        if ($sourceRank === null) {
            return 'place';
        }

        if ($target->rank > $sourceRank) {
            return 'promote';
        }

        return 'retain';
    }

    private function changeBlockedMessage(array $summary, YearLevel $target): string
    {
        $source = $summary['source_year_level']['name'] ?? 'the previous year level';

        if (($summary['source_year_level']['rank'] ?? null) !== null && $target->rank < $summary['source_year_level']['rank']) {
            return 'You cannot move this student below '.$source.'.';
        }

        if (! $summary['passed']) {
            return $summary['message'];
        }

        return 'This student cannot be moved to '.$target->name.'.';
    }

    private function resolveStatus(
        bool $hasSource,
        bool $passed,
        bool $incomplete,
        bool $alreadyAtNext,
        bool $isHighestLevel,
        bool $canPromote,
    ): string {
        if (! $hasSource) {
            return 'no_record';
        }

        if ($alreadyAtNext) {
            return 'already_promoted';
        }

        if ($incomplete) {
            return 'incomplete';
        }

        if ($passed && $isHighestLevel) {
            return 'completed';
        }

        if ($canPromote) {
            return 'eligible';
        }

        return 'failed';
    }

    /**
     * @param  array{failed_subjects: array<int, string>, missing_subjects: array<int, string>}  $result
     */
    private function statusMessage(
        string $status,
        ?YearLevel $sourceYearLevel,
        ?YearLevel $nextYearLevel,
        array $result,
    ): string {
        $sourceName = $sourceYearLevel?->name ?? 'the previous year level';

        return match ($status) {
            'eligible' => $sourceName.' has been passed. This student can be promoted to '.($nextYearLevel?->name ?? 'the next year level').'.',
            'already_promoted' => 'This student is already in '.($nextYearLevel?->name ?? 'the next year level').'.',
            'completed' => $sourceName.' has been passed. This is the highest year level.',
            'failed' => $sourceName.' has not been passed. Failed: '.$this->joinNames($result['failed_subjects']).'.',
            'incomplete' => 'Final grades for '.$sourceName.' are incomplete. Missing: '.$this->joinNames($result['missing_subjects']).'.',
            default => 'This student has no previous year level record to evaluate.',
        };
    }

    /**
     * @param  array<int, string>  $names
     */
    private function joinNames(array $names): string
    {
        if ($names === []) {
            return 'none';
        }

        $visible = array_slice($names, 0, 4);
        $remaining = count($names) - count($visible);
        $list = implode(', ', $visible);

        return $remaining > 0 ? $list.', and '.$remaining.' more' : $list;
    }

    private function yearLevelPayload(?YearLevel $yearLevel): ?array
    {
        if (! $yearLevel) {
            return null;
        }

        return [
            'id' => $yearLevel->id,
            'name' => $yearLevel->name,
            'code' => $yearLevel->code,
            'rank' => $yearLevel->rank,
        ];
    }

    private function buildRemarks(
        string $action,
        string $sourceName,
        string $sourceSchoolYear,
        string $targetName,
        ?string $extra,
    ): string {
        $date = now()->format('M d, Y');

        $note = match ($action) {
            'promote' => "Promoted from {$sourceName} ({$sourceSchoolYear}) to {$targetName} on {$date}.",
            'retain' => "Retained in {$targetName} on {$date}.",
            default => "Year level set to {$targetName} on {$date}.",
        };

        $extra = trim((string) $extra);

        return $extra === '' ? $note : $note.' '.$extra;
    }

    private function appendRemarks(?string $existing, string $note): string
    {
        $existing = trim((string) $existing);

        return $existing === '' ? $note : $existing."\n".$note;
    }
}
