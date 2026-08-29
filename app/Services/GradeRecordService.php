<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Subject;
use App\Models\YearLevel;

class GradeRecordService
{
    /**
     * @return array{
     *     records: array<int, array<string, mixed>>,
     *     yearLevels: \Illuminate\Support\Collection,
     *     sections: \Illuminate\Database\Eloquent\Collection,
     *     subjects: \Illuminate\Database\Eloquent\Collection,
     *     currentSchoolYear: string
     * }
     */
    public function pageData(?string $schoolYear = null): array
    {
        $schoolYear ??= SchoolSetting::currentSchoolYear();

        return [
            'records' => $this->records($schoolYear),
            'yearLevels' => YearLevel::ordered()->get(),
            'sections' => Section::query()
                ->with('yearLevel')
                ->withCount(['enrollments' => function ($query) {
                    $query->where('status', 'enrolled');
                }])
                ->where('school_year', $schoolYear)
                ->orderBy('year_level_id')
                ->orderBy('name')
                ->get(),
            'subjects' => Subject::query()->active()->orderBy('name')->get(['id', 'name', 'year_level_id']),
            'currentSchoolYear' => $schoolYear,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function records(string $schoolYear): array
    {
        $enrollments = Enrollment::query()
            ->with(['user', 'yearLevel', 'section'])
            ->where('school_year', $schoolYear)
            ->where('status', 'enrolled')
            ->orderBy('year_level_id')
            ->get();

        $subjectsByYearLevel = Subject::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->groupBy('year_level_id');

        $grades = Grade::query()
            ->where('school_year', $schoolYear)
            ->get()
            ->keyBy(fn (Grade $grade) => $grade->student_id.'-'.$grade->subject_id);

        $records = [];

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->user;
            if (! $student) {
                continue;
            }

            $subjects = $subjectsByYearLevel->get($enrollment->year_level_id, collect());

            foreach ($subjects as $subject) {
                $grade = $grades->get($student->id.'-'.$subject->id);
                $final = $grade?->final_grade;

                $records[] = [
                    'id' => $student->id.'-'.$subject->id,
                    'student_id' => $student->id,
                    'student_name' => trim($student->last_name.', '.$student->first_name),
                    'lrn' => $student->lrn,
                    'year_level_id' => $enrollment->year_level_id,
                    'year_level' => $enrollment->yearLevel?->name,
                    'section_id' => $enrollment->section_id,
                    'section' => $enrollment->section?->name,
                    'subject_id' => $subject->id,
                    'subject' => $subject->name,
                    'term_1' => $grade?->term_1,
                    'term_2' => $grade?->term_2,
                    'term_3' => $grade?->term_3,
                    'final_grade' => $final,
                    'remarks' => $final === null
                        ? 'Incomplete'
                        : ((float) $final >= StudentPromotionService::PASSING_GRADE ? 'Passed' : 'Failed'),
                ];
            }
        }

        return $records;
    }
}
