<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\StudentRequirement;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;

class PortalAssistantContext
{
    public function __construct(private TeacherAssistantContext $teacher) {}

    /**
     * Records the signed-in person is allowed to see. Other accounts stay out.
     */
    public function for(User $user): string
    {
        return match ($user->role) {
            'teacher' => $this->teacher->for($user),
            'student' => $this->student($user),
            'registrar' => $this->registrar($user),
            default => $this->administrator($user),
        };
    }

    public function guide(string $role): string
    {
        return match ($role) {
            'student' => <<<'TEXT'
Help them use the student portal:
- Dashboard is the home page.
- Student Profile shows their own information.
- Enrollment is where they see or submit enrollment.
- Requirements is where they upload admission documents.
- Enrolled Subjects lists the subjects for their section.
- Grades shows their own marks for each term.
- Attendance shows whether they were present, absent, late, or excused.
- Report Card opens SF9. Permanent Record opens SF10.
- Change Password asks for the current password, then the new password twice.
TEXT,
            'registrar' => <<<'TEXT'
Help them use the registrar portal:
- Dashboard is the home page.
- Year Levels lists the grade levels.
- Sections lists class sections and who is enrolled.
- Grading Records shows saved grades.
- Students lists enrolled students. Open a student to see enrollment.
- SP-10 Records holds permanent records.
- Enrollment Summary shows how many students are enrolled.
- Change Password asks for the current password, then the new password twice.
TEXT,
            'administrator' => <<<'TEXT'
Help them use the admin portal:
- Dashboard is the home page.
- Students, Admissions, Enrollments, Student Requirements, School Forms, and Enrollment Summary are under student records.
- Teachers and Teacher Assignments are under personnel.
- Year Levels, Academic Tracks, Sections, and Subjects are the academic setup.
- Accounts manages user accounts. Settings holds school settings and the admin password.
TEXT,
            default => <<<'TEXT'
Help them use the teacher portal:
- Dashboard is the home page. The left menu opens classes, grades, attendance, and school forms.
- Advisory shows the students in the teacher's advisory section.
- Subjects lists the subjects they teach. Open a subject to see its students.
- Grades is where they enter marks. Choose a class and a term, type the grade, or press Input grades by voice.
- Attendance: choose the class and the date, mark each student present, absent, late, or excused, then press Save attendance.
- School forms opens SF1, SF2, SF9, and SF10 to view or print.
- Change password asks for the current password, then the new password twice.
TEXT,
        };
    }

    private function student(User $student): string
    {
        $schoolYear = SchoolSetting::currentSchoolYear();
        $term = (int) SchoolSetting::current()->current_term;
        $enrollment = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('school_year', $schoolYear)
            ->with(['yearLevel', 'section.adviser'])
            ->first();

        $lines = [
            'These records belong only to the signed-in student. Do not invent a grade, attendance mark, or requirement.',
            'Signed-in student: '.$this->name($student).'. When they ask who they are, use this name.',
            'LRN: '.($student->lrn ?: 'none'),
            'School year: '.$schoolYear,
            'Current term: '.$term,
        ];

        if (! $enrollment) {
            $lines[] = 'ENROLLMENT: none for this school year.';
        } else {
            $adviser = $enrollment->section?->adviser;
            $lines[] = 'ENROLLMENT STATUS: '.$enrollment->status;
            $lines[] = 'YEAR LEVEL: '.($enrollment->yearLevel?->name ?: 'none');
            $lines[] = 'SECTION: '.($enrollment->section?->name ?: 'none');
            $lines[] = 'ADVISER: '.($adviser ? $this->name($adviser) : 'none');
        }

        $lines[] = 'GRADES:';
        if ($enrollment && $enrollment->status === 'enrolled') {
            $subjects = Subject::query()
                ->where('year_level_id', $enrollment->year_level_id)
                ->forSection($enrollment->section_id)
                ->active()
                ->orderBy('name')
                ->get();
            $grades = Grade::query()
                ->where('student_id', $student->id)
                ->where('school_year', $schoolYear)
                ->get()
                ->keyBy('subject_id');
            if ($subjects->isEmpty()) {
                $lines[] = 'No subjects are listed for this enrollment.';
            }
            foreach ($subjects->take(30) as $subject) {
                $grade = $grades->get($subject->id);
                $lines[] = '- '.$subject->name.': term 1 '.$this->mark($grade?->term_1)
                    .', term 2 '.$this->mark($grade?->term_2)
                    .', term 3 '.$this->mark($grade?->term_3)
                    .', final '.$this->mark($grade?->final_grade);
            }
        } else {
            $lines[] = 'None, because this student is not enrolled for the school year.';
        }

        $lines[] = 'ATTENDANCE IN THE LAST 14 DAYS:';
        $rows = Attendance::query()
            ->where('student_id', $student->id)
            ->where('school_year', $schoolYear)
            ->whereDate('attendance_date', '>=', now()->subDays(14)->toDateString())
            ->orderByDesc('attendance_date')
            ->get(['attendance_date', 'status']);
        if ($rows->isEmpty()) {
            $lines[] = 'None saved.';
        } else {
            foreach (Attendance::STATUSES as $status) {
                $lines[] = $status.': '.$rows->where('status', $status)->count();
            }
            $absent = $rows->where('status', 'absent')
                ->map(fn (Attendance $row) => $row->attendance_date->toDateString())
                ->unique()
                ->take(8)
                ->implode(', ');
            if ($absent !== '') {
                $lines[] = 'Absent dates: '.$absent;
            }
        }

        $lines[] = 'REQUIREMENTS:';
        $requirements = StudentRequirement::query()
            ->where('user_id', $student->id)
            ->get(['requirement_type', 'status']);
        if ($requirements->isEmpty()) {
            $lines[] = 'None on file.';
        }
        foreach ($requirements->take(20) as $requirement) {
            $lines[] = '- '.$requirement->requirement_type.': '.$requirement->status;
        }

        return implode("\n", $lines);
    }

    private function administrator(User $admin): string
    {
        $schoolYear = SchoolSetting::currentSchoolYear();
        $term = (int) SchoolSetting::current()->current_term;
        $students = User::query()->where('role', 'student')->count();
        $teachers = User::query()->where('role', 'teacher')->count();
        $pending = User::query()->where('role', 'student')->where('admission_status', 'pending')->count();
        $enrolled = Enrollment::query()->where('school_year', $schoolYear)->where('status', 'enrolled')->count();
        $sections = Section::query()->where('school_year', $schoolYear)->count();
        $subjects = Subject::query()->count();

        return implode("\n", [
            'These counts belong to the signed-in administrator. Do not invent a number that is not listed. Do not list private passwords or medical information.',
            'Signed-in administrator: '.$this->name($admin).'. When they ask who they are, use this name.',
            'School year: '.$schoolYear,
            'Current term: '.$term,
            'When they ask how many students the school has, use STUDENT COUNT.',
            'STUDENT COUNT: '.$students,
            'TEACHER COUNT: '.$teachers,
            'PENDING ADMISSIONS: '.$pending,
            'ENROLLED THIS YEAR: '.$enrolled,
            'SECTION COUNT: '.$sections,
            'SUBJECT COUNT: '.$subjects,
        ]);
    }

    private function registrar(User $registrar): string
    {
        $schoolYear = SchoolSetting::currentSchoolYear();
        $term = (int) SchoolSetting::current()->current_term;
        $levels = YearLevel::query()
            ->withCount(['enrollments' => function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)->where('status', 'enrolled');
            }])
            ->ordered()
            ->get();
        $enrolled = Enrollment::query()->where('school_year', $schoolYear)->where('status', 'enrolled')->count();
        $sections = Section::query()->where('school_year', $schoolYear)->count();
        $grades = Grade::query()->where('school_year', $schoolYear)->count();

        $lines = [
            'These counts belong to the signed-in registrar. Do not invent a number that is not listed.',
            'Signed-in registrar: '.$this->name($registrar).'. When they ask who they are, use this name.',
            'School year: '.$schoolYear,
            'Current term: '.$term,
            'When they ask how many students are enrolled, use ENROLLED COUNT.',
            'ENROLLED COUNT: '.$enrolled,
            'SECTION COUNT: '.$sections,
            'YEAR LEVEL COUNT: '.$levels->count(),
            'GRADE RECORD COUNT: '.$grades,
            'ENROLLED BY YEAR LEVEL:',
        ];
        if ($levels->isEmpty()) {
            $lines[] = 'None.';
        }
        foreach ($levels as $level) {
            $lines[] = '- '.$level->name.': '.$level->enrollments_count.' enrolled';
        }

        return implode("\n", $lines);
    }

    private function name(User $user): string
    {
        $name = trim(implode(' ', array_filter([
            $user->first_name,
            $user->middle_name,
            $user->last_name,
            $user->suffix,
        ])));

        return $name !== '' ? $name : 'User '.$user->id;
    }

    private function mark(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'none';
        }

        $number = (float) $value;

        return abs($number - round($number)) < 0.001
            ? (string) (int) round($number)
            : rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }
}
