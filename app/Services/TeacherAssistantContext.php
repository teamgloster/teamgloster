<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Collection;

class TeacherAssistantContext
{
    public function __construct(private TeacherAttendanceService $attendance) {}

    /**
     * Facts from the signed-in teacher's own classes. Other teachers' records are left out.
     */
    public function for(User $teacher): string
    {
        $schoolYear = SchoolSetting::currentSchoolYear();
        $term = (int) SchoolSetting::current()->current_term;
        $sections = $this->advisorySections($teacher, $schoolYear);
        $classes = $this->attendance->classesFor($teacher, $schoolYear);
        $subjectClasses = $classes->where('kind', 'subject')->values();

        $sectionIds = $sections->pluck('id')
            ->merge($subjectClasses->pluck('section_id'))
            ->filter()
            ->unique()
            ->values();

        $enrollments = $this->enrollments($sectionIds);
        $advisoryIds = $sections->pluck('id');
        $advisoryStudents = $enrollments
            ->filter(fn (Enrollment $enrollment) => $advisoryIds->contains($enrollment->section_id))
            ->unique('user_id')
            ->sortBy(fn (Enrollment $enrollment) => $this->sortName($enrollment->user))
            ->values();

        $taughtStudents = $enrollments
            ->filter(fn (Enrollment $enrollment) => $subjectClasses->contains(
                fn (array $class) => (int) $class['section_id'] === (int) $enrollment->section_id
            ))
            ->unique('user_id');

        $lines = [
            'These records belong only to the signed-in teacher. Answer from them. Do not invent a name, count, grade, or attendance mark that is not listed.',
            'Signed-in teacher: '.$this->studentName($teacher).'. When they ask who they are, use this name.',
            'School year: '.$schoolYear,
            'Current term: '.$term,
            'ADVISORY STUDENT COUNT: '.$advisoryStudents->count(),
        ];

        if ($sections->isEmpty()) {
            $lines[] = 'This teacher is not the adviser of a section.';
            $lines[] = 'When they ask how many students they have, use the unique count of students in the classes they teach.';
        } else {
            $lines[] = 'When they ask how many students they have and do not name a subject, say ADVISORY STUDENT COUNT first. If the unique count of students in the classes they teach is different, mention that number too.';
            $lines[] = 'ADVISORY CLASSES:';
            foreach ($sections as $section) {
                $count = $advisoryStudents
                    ->filter(fn (Enrollment $enrollment) => (int) $enrollment->section_id === (int) $section->id)
                    ->count();
                $lines[] = '- '.$this->sectionLabel($section).': '.$count.' enrolled students';
            }
            $lines[] = 'ADVISORY STUDENTS:';
            $lines = array_merge($lines, $this->rosterLines($advisoryStudents));
        }

        $lines[] = 'STUDENTS IN CLASSES YOU TEACH, COUNTED ONCE: '.$taughtStudents->count();
        $lines[] = 'SUBJECTS YOU TEACH:';
        if ($subjectClasses->isEmpty()) {
            $lines[] = 'None assigned for this school year.';
        }

        $grades = $this->grades(
            $subjectClasses->pluck('subject_id'),
            $enrollments->pluck('user_id'),
            $schoolYear,
        );

        foreach ($subjectClasses as $class) {
            $classStudents = $enrollments
                ->filter(fn (Enrollment $enrollment) => (int) $enrollment->section_id === (int) $class['section_id'])
                ->unique('user_id')
                ->sortBy(fn (Enrollment $enrollment) => $this->sortName($enrollment->user))
                ->values();
            $lines[] = '- '.$class['label'].': '.$classStudents->count().' students';
            foreach ($classStudents->take(60) as $enrollment) {
                $grade = $grades->get($enrollment->user_id.'-'.$class['subject_id']);
                $lines[] = '  '.$this->studentLine($enrollment->user).': '.$this->gradeText($grade);
            }
            if ($classStudents->count() > 60) {
                $lines[] = '  and '.($classStudents->count() - 60).' more students in this class';
            }
        }

        $lines[] = 'ATTENDANCE YOU RECORDED IN THE LAST 14 DAYS:';
        $lines = array_merge($lines, $this->attendanceLines($teacher, $schoolYear, $classes, $enrollments));

        $text = implode("\n", $lines);
        if (strlen($text) > 24000) {
            $text = substr($text, 0, 24000)."\nSome later rows were left out because the list is long.";
        }

        return $text;
    }

    /**
     * @return Collection<int, Section>
     */
    private function advisorySections(User $teacher, string $schoolYear): Collection
    {
        $sections = Section::query()
            ->where('adviser_id', $teacher->id)
            ->with('yearLevel')
            ->orderBy('name')
            ->get();

        $current = $sections
            ->filter(fn (Section $section) => blank($section->school_year) || $section->school_year === $schoolYear)
            ->values();

        return $current->isNotEmpty() ? $current : $sections;
    }

    /**
     * @param  Collection<int, int|string>  $sectionIds
     * @return Collection<int, Enrollment>
     */
    private function enrollments(Collection $sectionIds): Collection
    {
        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return Enrollment::query()
            ->whereIn('section_id', $sectionIds)
            ->where('status', 'enrolled')
            ->with(['user' => function ($query) {
                $query->select('id', 'first_name', 'middle_name', 'last_name', 'suffix', 'lrn');
            }])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->user !== null)
            ->values();
    }

    /**
     * @param  Collection<int, int|string|null>  $subjectIds
     * @param  Collection<int, int|string|null>  $studentIds
     * @return Collection<string, Grade>
     */
    private function grades(Collection $subjectIds, Collection $studentIds, string $schoolYear): Collection
    {
        $subjectIds = $subjectIds->filter()->unique()->values();
        $studentIds = $studentIds->filter()->unique()->values();
        if ($subjectIds->isEmpty() || $studentIds->isEmpty()) {
            return collect();
        }

        return Grade::query()
            ->where('school_year', $schoolYear)
            ->whereIn('subject_id', $subjectIds)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy(fn (Grade $grade) => $grade->student_id.'-'.$grade->subject_id);
    }

    /**
     * @param  Collection<int, Enrollment>  $students
     * @return list<string>
     */
    private function rosterLines(Collection $students): array
    {
        if ($students->isEmpty()) {
            return ['None enrolled.'];
        }

        $lines = $students
            ->take(80)
            ->map(fn (Enrollment $enrollment) => '- '.$this->studentLine($enrollment->user))
            ->all();

        if ($students->count() > 80) {
            $lines[] = '- and '.($students->count() - 80).' more enrolled students';
        }

        return $lines;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $classes
     * @param  Collection<int, Enrollment>  $enrollments
     * @return list<string>
     */
    private function attendanceLines(User $teacher, string $schoolYear, Collection $classes, Collection $enrollments): array
    {
        $rows = Attendance::query()
            ->where('teacher_id', $teacher->id)
            ->where('school_year', $schoolYear)
            ->whereDate('attendance_date', '>=', now()->subDays(14)->toDateString())
            ->orderByDesc('attendance_date')
            ->get(['student_id', 'class_key', 'attendance_date', 'status']);

        if ($rows->isEmpty()) {
            return ['None saved.'];
        }

        $names = $enrollments
            ->mapWithKeys(fn (Enrollment $enrollment) => [$enrollment->user_id => $this->studentName($enrollment->user)]);
        $missingIds = $rows->pluck('student_id')->unique()->diff($names->keys())->filter()->values();
        if ($missingIds->isNotEmpty()) {
            User::query()
                ->whereIn('id', $missingIds)
                ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix'])
                ->each(function (User $student) use ($names) {
                    $names->put($student->id, $this->studentName($student));
                });
        }

        $labels = $classes->mapWithKeys(fn (array $class) => [$class['key'] => $class['label']]);
        $groups = $rows->groupBy(fn (Attendance $row) => $row->attendance_date->toDateString().'|'.$row->class_key);

        $lines = [];
        foreach ($groups->take(8) as $key => $group) {
            [$date, $classKey] = explode('|', (string) $key, 2);
            $label = $labels->get($classKey, $classKey);
            $counts = [];
            $named = [];
            foreach (Attendance::STATUSES as $status) {
                $matching = $group->where('status', $status);
                $counts[] = $status.' '.$matching->count();
                if ($status === 'present') {
                    continue;
                }
                $people = $matching
                    ->map(fn (Attendance $row) => $names->get($row->student_id, 'a student'))
                    ->take(12)
                    ->implode(', ');
                if ($people !== '') {
                    $extra = $matching->count() > 12 ? ' and '.($matching->count() - 12).' more' : '';
                    $named[] = $status.': '.$people.$extra;
                }
            }
            $detail = $named === [] ? '' : ' ('.implode('; ', $named).')';
            $lines[] = '- '.$date.', '.$label.': '.implode(', ', $counts).$detail;
        }

        if ($groups->count() > 8) {
            $lines[] = '- Older attendance days were left out.';
        }

        return $lines;
    }

    private function sectionLabel(Section $section): string
    {
        $year = $section->yearLevel?->name;

        return trim(($year ? $year.' ' : '').$section->name);
    }

    private function gradeText(?Grade $grade): string
    {
        if (! $grade) {
            return 'no grade yet';
        }

        return 'term 1 '.$this->mark($grade->term_1)
            .', term 2 '.$this->mark($grade->term_2)
            .', term 3 '.$this->mark($grade->term_3)
            .', final '.$this->mark($grade->final_grade);
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

    private function studentLine(User $student): string
    {
        $lrn = trim((string) $student->lrn);

        return $lrn !== ''
            ? $this->studentName($student).', LRN '.$lrn
            : $this->studentName($student);
    }

    private function studentName(User $student): string
    {
        $name = trim(implode(' ', array_filter([
            $student->first_name,
            $student->middle_name,
            $student->last_name,
            $student->suffix,
        ])));

        return $name !== '' ? $name : 'Student '.$student->id;
    }

    private function sortName(User $student): string
    {
        return strtolower($student->last_name.' '.$student->first_name);
    }
}
