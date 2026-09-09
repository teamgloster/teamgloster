<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SchoolFormService
{
    public function sf9(User $student, ?string $schoolYear = null, ?int $term = null): array
    {
        $settings = SchoolSetting::current();
        $schoolYear = $schoolYear ?: $settings->current_school_year;
        $termLimit = in_array($term, [1, 2, 3], true) ? $term : 3;
        $isPartialTerm = $term !== null && $term < 3;

        $enrollment = $this->enrollmentForYear($student, $schoolYear)
            ?? $this->latestEnrollment($student);

        if ($enrollment) {
            $schoolYear = $enrollment->school_year;
        }

        $yearLevel = $enrollment?->yearLevel;
        $section = $enrollment?->section;
        $adviser = $section?->adviser;
        $rank = $yearLevel?->rank ?? $this->rankFromApplying($student);
        $isShs = $rank >= 11;

        $subjects = $yearLevel
            ? Subject::query()
                ->where('year_level_id', $yearLevel->id)
                ->forSection($section?->id)
                ->active()
                ->orderBy('name')
                ->get()
                ->sortBy(fn (Subject $subject) => $this->subjectTypeOrder($subject->subject_type))
                ->values()
            : collect();

        $grades = $this->gradesForYear($student, $schoolYear);

        $rows = $subjects->map(function (Subject $subject) use ($grades, $termLimit, $isPartialTerm) {
            $grade = $grades->get($subject->id);
            $term1 = $termLimit >= 1 ? $grade?->term_1 : null;
            $term2 = $termLimit >= 2 ? $grade?->term_2 : null;
            $term3 = $termLimit >= 3 ? $grade?->term_3 : null;
            $final = $this->termStanding($grade, $termLimit, $isPartialTerm);

            return [
                'name' => $subject->name,
                'type' => $this->subjectTypeLabel($subject->subject_type),
                'semester' => $this->semesterLabel($subject->semester),
                'term_1' => $this->formatTermRating($term1),
                'term_2' => $this->formatTermRating($term2),
                'term_3' => $this->formatTermRating($term3),
                'final' => $final,
                'remarks' => $this->remarks($final),
                'descriptor' => $this->descriptor($final),
            ];
        })->values()->all();

        $finals = collect($rows)
            ->pluck('final')
            ->filter(fn ($value) => $value !== null)
            ->values();

        $generalAverage = $this->generalAverage($finals->all());

        return [
            'form' => [
                'code' => $isShs ? 'SF9-SHS' : 'SF9-JHS',
                'title' => "Learner's Performance Report",
                'former' => 'Formerly Form 138 / SF9',
                'legal' => 'DepEd Order No. 58, s. 2017',
            ],
            'school' => $this->schoolPayload($settings),
            'learner' => $this->learnerPayload($student, $schoolYear),
            'class_info' => [
                'grade' => $yearLevel?->name,
                'section' => $section?->name,
                'school_year' => $schoolYear,
                'adviser' => $adviser ? $this->personName($adviser) : null,
                'track_strand' => $isShs ? $student->preferred_strand : null,
                'term' => $term,
                'term_label' => $term ? 'Term '.$term : 'All terms',
            ],
            'learning_areas' => $rows,
            'general_average' => $generalAverage,
            'general_average_descriptor' => $this->descriptor($generalAverage),
            'general_average_remarks' => $this->remarks($generalAverage !== null ? (float) $generalAverage : null),
            'general_average_complete' => $subjects->isNotEmpty() && $finals->count() === $subjects->count(),
            'attendance_months' => $this->sf9AttendanceMonths(),
            'descriptors' => $this->sf9DescriptorScale(),
            'parent' => $student->guardian_full_name ?: $student->father_name ?: $student->mother_name,
            'school_head' => $settings->school_head,
            'next_grade' => $this->nextGradeLabel($rank),
            'generated_term' => $term,
        ];
    }

    public function sf10(User $student): array
    {
        $settings = SchoolSetting::current();

        $enrollments = Enrollment::query()
            ->where('user_id', $student->id)
            ->whereIn('status', ['enrolled', 'approved'])
            ->with(['yearLevel', 'section.adviser'])
            ->orderBy('school_year')
            ->orderBy('id')
            ->get();

        $latestRank = (int) $enrollments
            ->map(fn (Enrollment $enrollment) => $enrollment->yearLevel?->rank ?? 0)
            ->max();

        if ($latestRank === 0) {
            $latestRank = $this->rankFromApplying($student);
        }

        $jhsRecords = $this->buildRecords($student, $enrollments, 'JHS');
        $shsRecords = $this->buildRecords($student, $enrollments, 'SHS');

        $showJhs = $jhsRecords !== [] || $latestRank < 11;
        $showShs = $shsRecords !== [] || $latestRank >= 11;

        return [
            'form' => [
                'title' => "Learner's Permanent Academic Record",
                'former' => 'Formerly Form 137',
                'legal' => 'DepEd Order No. 58, s. 2017',
            ],
            'school' => $this->schoolPayload($settings),
            'learner' => $this->learnerPayload($student, $settings->current_school_year),
            'family' => [
                'father' => $student->father_name,
                'mother' => $student->mother_name,
                'guardian' => $student->guardian_full_name,
            ],
            'eligibility' => [
                'previous_school' => $student->previous_school,
                'previous_gwa' => $student->previous_gwa !== null
                    ? $this->wholeRating($student->previous_gwa)
                    : null,
                'year_level_applying' => $student->year_level_applying,
            ],
            'show_jhs' => $showJhs,
            'show_shs' => $showShs,
            'jhs_records' => $jhsRecords,
            'shs_records' => $shsRecords,
            'school_head' => $settings->school_head,
            'next_grade' => $this->nextGradeLabel($latestRank),
        ];
    }

    public function sf1(Section $section, ?string $schoolYear = null): array
    {
        $settings = SchoolSetting::current();
        $section->loadMissing(['yearLevel', 'adviser']);
        $schoolYear = $schoolYear ?: ($section->school_year ?: $settings->current_school_year);

        $enrollments = Enrollment::query()
            ->where('section_id', $section->id)
            ->where('school_year', $schoolYear)
            ->whereIn('status', ['enrolled', 'approved'])
            ->with('user')
            ->get()
            ->sortBy(function (Enrollment $enrollment) {
                $user = $enrollment->user;

                return strtoupper(trim(($user?->last_name ?? '').' '.($user?->first_name ?? '')));
            })
            ->values();

        $learners = $enrollments
            ->filter(fn (Enrollment $enrollment) => $enrollment->user)
            ->values()
            ->map(function (Enrollment $enrollment, int $index) use ($schoolYear) {
                $user = $enrollment->user;
                $birthDate = $user->date_of_birth ? Carbon::parse($user->date_of_birth) : null;
                $address = collect([$user->barangay, $user->municipality, $user->province])
                    ->filter()
                    ->implode(', ');

                return [
                    'no' => $index + 1,
                    'lrn' => $user->lrn,
                    'name' => $this->learnerFormalName($user),
                    'sex' => $this->sexLabel($user->gender),
                    'birth_date' => $birthDate?->format('M j, Y'),
                    'age' => $this->ageAsOfSchoolYear($birthDate, $schoolYear),
                    'mother_tongue' => null,
                    'ip' => null,
                    'religion' => null,
                    'address' => $address !== '' ? $address : null,
                    'father' => $user->father_name,
                    'mother' => $user->mother_name,
                    'guardian' => $user->guardian_full_name,
                    'contact' => $user->guardian_contact_no ?: $user->father_contact ?: $user->mother_contact,
                    'remarks' => $this->enrollmentRemarks($enrollment),
                    'is_male' => strtolower((string) $user->gender) === 'male',
                ];
            })
            ->all();

        $male = collect($learners)->where('is_male', true)->count();
        $female = count($learners) - $male;

        return [
            'form' => [
                'code' => 'SF1',
                'title' => 'School Register',
                'legal' => 'DepEd Order No. 58, s. 2017',
            ],
            'school' => $this->schoolPayload($settings),
            'class_info' => [
                'grade' => $section->yearLevel?->name,
                'section' => $section->name,
                'school_year' => $schoolYear,
                'adviser' => $section->adviser ? $this->personName($section->adviser) : null,
            ],
            'learners' => $learners,
            'totals' => [
                'male' => $male,
                'female' => $female,
                'total' => count($learners),
            ],
            'school_head' => $settings->school_head,
        ];
    }

    public function sf2(Section $section, int $month, ?string $schoolYear = null): array
    {
        $settings = SchoolSetting::current();
        $section->loadMissing(['yearLevel', 'adviser']);
        $schoolYear = $schoolYear ?: ($section->school_year ?: $settings->current_school_year);
        $month = max(1, min(12, $month));
        $calendarYear = $this->calendarYearForMonth($schoolYear, $month);
        $start = Carbon::create($calendarYear, $month, 1);
        $daysInMonth = $start->daysInMonth;

        $days = [];
        for ($day = 1; $day <= 31; $day++) {
            $valid = $day <= $daysInMonth;
            $date = $valid ? Carbon::create($calendarYear, $month, $day) : null;
            $days[] = [
                'day' => $day,
                'valid' => $valid,
                'weekend' => $date ? $date->isWeekend() : false,
                'weekday' => $date ? $date->format('D') : null,
            ];
        }

        $enrollments = Enrollment::query()
            ->where('section_id', $section->id)
            ->where('school_year', $schoolYear)
            ->whereIn('status', ['enrolled', 'approved'])
            ->with('user')
            ->get()
            ->sortBy(function (Enrollment $enrollment) {
                $user = $enrollment->user;

                return strtoupper(trim(($user?->last_name ?? '').' '.($user?->first_name ?? '')));
            })
            ->values();

        $learners = $enrollments
            ->filter(fn (Enrollment $enrollment) => $enrollment->user)
            ->values()
            ->map(function (Enrollment $enrollment, int $index) {
                $user = $enrollment->user;

                return [
                    'no' => $index + 1,
                    'name' => $this->learnerFormalName($user),
                    'sex' => $this->sexLabel($user->gender),
                    'is_male' => strtolower((string) $user->gender) === 'male',
                    'remarks' => $this->enrollmentRemarks($enrollment),
                ];
            })
            ->all();

        $male = collect($learners)->where('is_male', true)->count();
        $female = count($learners) - $male;

        return [
            'form' => [
                'code' => 'SF2',
                'title' => 'Daily Attendance Report of Learners',
                'legal' => 'DepEd Order No. 58, s. 2017',
            ],
            'school' => $this->schoolPayload($settings),
            'class_info' => [
                'grade' => $section->yearLevel?->name,
                'section' => $section->name,
                'school_year' => $schoolYear,
                'adviser' => $section->adviser ? $this->personName($section->adviser) : null,
                'month' => $start->format('F'),
                'year' => $calendarYear,
                'month_number' => $month,
            ],
            'days' => $days,
            'learners' => $learners,
            'totals' => [
                'male' => $male,
                'female' => $female,
                'total' => count($learners),
            ],
            'school_head' => $settings->school_head,
        ];
    }

    private function enrollmentRemarks(Enrollment $enrollment): ?string
    {
        $type = match ($enrollment->enrollment_type) {
            'new' => 'New',
            'old' => 'Old/Continuing',
            'transferee' => 'Transferred in',
            'returnee' => 'Returnee',
            default => null,
        };

        if ($enrollment->status === 'dropped') {
            return trim(($type ? $type.' / ' : '').'Dropped');
        }

        return $type;
    }

    private function calendarYearForMonth(string $schoolYear, int $month): int
    {
        $startYear = (int) substr($schoolYear, 0, 4);

        return $month >= 8 ? $startYear : $startYear + 1;
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return list<array<string, mixed>>
     */
    private function buildRecords(User $student, Collection $enrollments, string $level): array
    {
        $records = [];

        foreach ($enrollments as $enrollment) {
            $rank = $enrollment->yearLevel?->rank ?? 0;
            $isShs = $rank >= 11;

            if ($level === 'JHS' && $isShs) {
                continue;
            }

            if ($level === 'SHS' && ! $isShs) {
                continue;
            }

            $subjects = Subject::query()
                ->where('year_level_id', $enrollment->year_level_id)
                ->forSection($enrollment->section_id)
                ->active()
                ->orderBy('name')
                ->get();

            if ($isShs && in_array($enrollment->semester, ['first', 'second'], true)) {
                $subjects = $subjects->filter(function (Subject $subject) use ($enrollment) {
                    return in_array($subject->semester, [$enrollment->semester, 'full_year', null, ''], true);
                })->values();
            }

            $subjects = $subjects
                ->sortBy(fn (Subject $subject) => $this->subjectTypeOrder($subject->subject_type))
                ->values();

            $grades = $this->gradesForYear($student, $enrollment->school_year);

            $rows = $subjects->map(function (Subject $subject) use ($grades) {
                $grade = $grades->get($subject->id);
                $final = $this->wholeRating($grade?->final_grade);

                return [
                    'name' => $subject->name,
                    'type' => $subject->subject_type,
                    'type_label' => $this->subjectTypeLabel($subject->subject_type),
                    'semester' => $this->semesterLabel($subject->semester),
                    'term_1' => $this->formatTermRating($grade?->term_1),
                    'term_2' => $this->formatTermRating($grade?->term_2),
                    'term_3' => $this->formatTermRating($grade?->term_3),
                    'final' => $final,
                    'remarks' => $this->remarks($final),
                ];
            });

            $groups = $isShs
                ? $this->groupShsSubjects($rows)
                : [[
                    'label' => 'Learning Areas',
                    'subjects' => $rows->values()->all(),
                ]];

            $finals = $rows
                ->pluck('final')
                ->filter(fn ($value) => $value !== null)
                ->values();

            $generalAverage = $this->generalAverage($finals->all());

            $records[] = [
                'grade' => $enrollment->yearLevel?->name,
                'grade_rank' => $rank,
                'section' => $enrollment->section?->name,
                'school_year' => $enrollment->school_year,
                'semester' => $isShs ? $this->semesterLabel($enrollment->semester) : null,
                'adviser' => $enrollment->section?->adviser
                    ? $this->personName($enrollment->section->adviser)
                    : null,
                'classified_as' => $enrollment->yearLevel?->name,
                'groups' => $groups,
                'general_average' => $generalAverage,
                'general_average_descriptor' => $this->descriptor($generalAverage),
                'remarks' => $this->remarks($generalAverage !== null ? (float) $generalAverage : null),
            ];
        }

        return $records;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return list<array{label: string, subjects: list<array<string, mixed>>}>
     */
    private function groupShsSubjects(Collection $rows): array
    {
        $order = ['core', 'applied', 'specialized', 'elective'];
        $groups = [];

        foreach ($order as $type) {
            $subjects = $rows->where('type', $type)->values()->all();
            if ($subjects === []) {
                continue;
            }

            $groups[] = [
                'label' => $this->subjectTypeLabel($type),
                'subjects' => $subjects,
            ];
        }

        return $groups;
    }

    private function enrollmentForYear(User $student, string $schoolYear): ?Enrollment
    {
        return Enrollment::query()
            ->where('user_id', $student->id)
            ->where('school_year', $schoolYear)
            ->with(['yearLevel', 'section.adviser'])
            ->orderBy('id')
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => match ($enrollment->status) {
                'enrolled' => 0,
                'approved' => 1,
                'pending' => 2,
                default => 3,
            })
            ->first();
    }

    private function latestEnrollment(User $student): ?Enrollment
    {
        return Enrollment::query()
            ->where('user_id', $student->id)
            ->whereIn('status', ['enrolled', 'approved'])
            ->with(['yearLevel', 'section.adviser'])
            ->orderByDesc('school_year')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return Collection<int, Grade>
     */
    private function gradesForYear(User $student, string $schoolYear): Collection
    {
        return Grade::query()
            ->where('student_id', $student->id)
            ->where('school_year', $schoolYear)
            ->get()
            ->keyBy('subject_id');
    }

    /**
     * @return array<string, mixed>
     */
    private function schoolPayload(SchoolSetting $settings): array
    {
        return [
            'school_name' => $settings->school_name,
            'school_id' => $settings->school_id,
            'region' => $settings->region,
            'division' => $settings->division,
            'district' => $settings->district,
            'address' => $settings->address,
            'municipality' => $settings->municipality,
            'province' => $settings->province,
            'school_head' => $settings->school_head,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function learnerPayload(User $student, string $schoolYear): array
    {
        $birthDate = $student->date_of_birth
            ? Carbon::parse($student->date_of_birth)
            : null;

        $address = collect([
            $student->barangay,
            $student->municipality,
            $student->province,
        ])->filter()->implode(', ');

        return [
            'lrn' => $student->lrn,
            'last_name' => $student->last_name,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'suffix' => $student->suffix,
            'full_name' => $this->learnerFormalName($student),
            'sex' => $this->sexLabel($student->gender),
            'birth_date' => $birthDate?->format('F j, Y'),
            'age' => $this->ageAsOfSchoolYear($birthDate, $schoolYear),
            'address' => $address !== '' ? $address : null,
        ];
    }

    private function learnerFormalName(User $student): string
    {
        $middle = $student->middle_name ? ' '.$student->middle_name : '';
        $suffix = $student->suffix ? ' '.$student->suffix : '';

        return trim(strtoupper($student->last_name).', '.strtoupper($student->first_name).strtoupper($middle).strtoupper($suffix));
    }

    private function personName(User $user): string
    {
        $middleInitial = $user->middle_name
            ? ' '.strtoupper(substr($user->middle_name, 0, 1)).'.'
            : '';
        $suffix = $user->suffix ? ' '.$user->suffix : '';

        return trim($user->first_name.$middleInitial.' '.$user->last_name.$suffix);
    }

    private function sexLabel(?string $gender): ?string
    {
        return match (strtolower((string) $gender)) {
            'male' => 'Male',
            'female' => 'Female',
            default => $gender ?: null,
        };
    }

    private function ageAsOfSchoolYear(?Carbon $birthDate, string $schoolYear): ?int
    {
        if (! $birthDate) {
            return null;
        }

        $startYear = (int) substr($schoolYear, 0, 4);
        if ($startYear < 1900) {
            return $birthDate->age;
        }

        return $birthDate->diff(Carbon::create($startYear, 8, 1))->y;
    }

    private function formatTermRating(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = (float) $value;

        return fmod($number, 1.0) === 0.0
            ? (string) (int) $number
            : number_format($number, 2, '.', '');
    }

    private function wholeRating(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) round((float) $value);
    }

    private function termStanding(?Grade $grade, int $termLimit, bool $isPartialTerm): ?int
    {
        if (! $isPartialTerm) {
            return $this->wholeRating($grade?->final_grade);
        }

        $available = [];
        if ($termLimit >= 1 && $grade?->term_1 !== null && $grade->term_1 !== '') {
            $available[] = (float) $grade->term_1;
        }
        if ($termLimit >= 2 && $grade?->term_2 !== null && $grade->term_2 !== '') {
            $available[] = (float) $grade->term_2;
        }
        if ($termLimit >= 3 && $grade?->term_3 !== null && $grade->term_3 !== '') {
            $available[] = (float) $grade->term_3;
        }

        if ($available === []) {
            return null;
        }

        return $this->wholeRating(array_sum($available) / count($available));
    }

    /**
     * @param  list<int>  $finals
     */
    private function generalAverage(array $finals): ?int
    {
        if ($finals === []) {
            return null;
        }

        return (int) round(array_sum($finals) / count($finals));
    }

    private function remarks(?float $grade): ?string
    {
        if ($grade === null) {
            return null;
        }

        return $grade >= 75 ? 'Passed' : 'Failed';
    }

    private function descriptor(?float $grade): ?string
    {
        if ($grade === null) {
            return null;
        }

        return match (true) {
            $grade >= 90 => 'Outstanding',
            $grade >= 85 => 'Very Satisfactory',
            $grade >= 80 => 'Satisfactory',
            $grade >= 75 => 'Fairly Satisfactory',
            default => 'Did Not Meet Expectations',
        };
    }

    /**
     * @return list<array{label: string, scale: string}>
     */
    private function descriptorScale(): array
    {
        return [
            ['label' => 'Outstanding', 'scale' => '90–100'],
            ['label' => 'Very Satisfactory', 'scale' => '85–89'],
            ['label' => 'Satisfactory', 'scale' => '80–84'],
            ['label' => 'Fairly Satisfactory', 'scale' => '75–79'],
            ['label' => 'Did Not Meet Expectations', 'scale' => 'Below 75'],
        ];
    }

    /**
     * @return list<array{core_value: string, behavior: string}>
     */
    private function observedValuesTemplate(): array
    {
        return [
            [
                'core_value' => 'Maka-Diyos',
                'behavior' => "Expresses one's spiritual beliefs while respecting the spiritual beliefs of others",
            ],
            [
                'core_value' => 'Maka-Diyos',
                'behavior' => 'Shows adherence to ethical principles by upholding truth',
            ],
            [
                'core_value' => 'Makatao',
                'behavior' => 'Is sensitive to individual, social, and cultural differences',
            ],
            [
                'core_value' => 'Makatao',
                'behavior' => 'Demonstrates contributions toward solidarity',
            ],
            [
                'core_value' => 'Makakalikasan',
                'behavior' => 'Cares for the environment and utilizes resources wisely, judiciously, and economically',
            ],
            [
                'core_value' => 'Makabansa',
                'behavior' => 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen',
            ],
            [
                'core_value' => 'Makabansa',
                'behavior' => 'Demonstrates appropriate behavior in carrying out activities in the school, community, and country',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function attendanceMonths(): array
    {
        return ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Total'];
    }

    /**
     * @return list<string>
     */
    private function sf9AttendanceMonths(): array
    {
        return ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'Total'];
    }

    /**
     * @return list<array{grade: string, description: string, remarks: string}>
     */
    private function sf9DescriptorScale(): array
    {
        return [
            ['grade' => '90–100', 'description' => 'Advancing', 'remarks' => 'Passed'],
            ['grade' => '80–89', 'description' => 'Benchmarking', 'remarks' => 'Passed'],
            ['grade' => '75–79', 'description' => 'Connecting', 'remarks' => 'Passed'],
            ['grade' => '65–74', 'description' => 'Developing', 'remarks' => 'Failed'],
            ['grade' => '0–64', 'description' => 'Emerging', 'remarks' => 'Failed'],
        ];
    }

    private function subjectTypeOrder(?string $type): int
    {
        return match ($type) {
            'core' => 1,
            'applied' => 2,
            'specialized' => 3,
            'elective' => 4,
            default => 5,
        };
    }

    private function subjectTypeLabel(?string $type): string
    {
        return match ($type) {
            'core' => 'Core Subjects',
            'applied' => 'Applied Track Subjects',
            'specialized' => 'Specialized Subjects',
            'elective' => 'Electives',
            default => 'Learning Areas',
        };
    }

    private function semesterLabel(?string $semester): ?string
    {
        return match ($semester) {
            'first' => 'First Semester',
            'second' => 'Second Semester',
            'full_year' => 'Full Year',
            default => $semester,
        };
    }

    private function rankFromApplying(User $student): int
    {
        if (preg_match('/(\d+)/', (string) $student->year_level_applying, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    private function nextGradeLabel(int $rank): ?string
    {
        return match (true) {
            $rank === 10 => 'Grade 11',
            $rank === 12 => 'Higher Education / TESDA',
            $rank >= 7 && $rank < 12 => 'Grade '.($rank + 1),
            default => null,
        };
    }
}
