<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\SectionSubjectTeacher;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;

class TeacherAttendanceService
{
    /**
     * Classes this teacher can take attendance for in the current school year.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function classesFor(User $teacher, string $schoolYear): Collection
    {
        $classes = collect();

        $assignments = SectionSubjectTeacher::query()
            ->where('teacher_id', $teacher->id)
            ->with(['section.yearLevel', 'subject'])
            ->get()
            ->filter(fn (SectionSubjectTeacher $assignment) => $this->assignmentMatchesSchoolYear($assignment, $schoolYear));

        $subjects = $teacher->teachableSubjects()
            ->with(['yearLevel', 'sections.yearLevel'])
            ->get();

        foreach ($subjects as $subject) {
            $subjectAssignments = $assignments->filter(
                fn (SectionSubjectTeacher $assignment) => (int) $assignment->subject_id === (int) $subject->id
            );

            if ($subjectAssignments->isNotEmpty()) {
                foreach ($subjectAssignments as $assignment) {
                    if ($assignment->section && $assignment->subject) {
                        $classes->push($this->subjectClass($assignment->section, $assignment->subject));
                    }
                }

                continue;
            }

            if (! $subject->year_level_id) {
                continue;
            }

            $sectionIds = $subject->sections->pluck('id');
            $enrollments = Enrollment::query()
                ->where('year_level_id', $subject->year_level_id)
                ->where('school_year', $schoolYear)
                ->where('status', 'enrolled')
                ->when($sectionIds->isNotEmpty(), fn ($query) => $query->whereIn('section_id', $sectionIds))
                ->with('section.yearLevel')
                ->get();

            foreach ($enrollments as $enrollment) {
                if ($enrollment->section) {
                    $classes->push($this->subjectClass($enrollment->section, $subject));
                }
            }
        }

        foreach ($assignments as $assignment) {
            if (! $assignment->section || ! $assignment->subject) {
                continue;
            }

            $classes->push($this->subjectClass($assignment->section, $assignment->subject));
        }

        $advisorySections = Section::query()
            ->where('adviser_id', $teacher->id)
            ->where('school_year', $schoolYear)
            ->with('yearLevel')
            ->orderBy('name')
            ->get();

        foreach ($advisorySections as $section) {
            $classes->push($this->advisoryClass($section));
        }

        $sectionIds = $classes->pluck('section_id')->unique()->filter()->values();
        $counts = $sectionIds->isEmpty()
            ? collect()
            : Enrollment::query()
                ->where('school_year', $schoolYear)
                ->where('status', 'enrolled')
                ->whereIn('section_id', $sectionIds)
                ->selectRaw('section_id, COUNT(*) as total')
                ->groupBy('section_id')
                ->pluck('total', 'section_id')
                ->mapWithKeys(fn ($total, $sectionId) => [(int) $sectionId => (int) $total]);

        return $classes
            ->unique('key')
            ->map(function (array $class) use ($counts) {
                $class['student_count'] = (int) ($counts[$class['section_id']] ?? 0);

                return $class;
            })
            ->sortBy('label')
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    public function studentsFor(User $teacher, string $schoolYear, string $classKey): Collection
    {
        $class = $this->classesFor($teacher, $schoolYear)->firstWhere('key', $classKey);
        if (! $class) {
            return collect();
        }

        return Enrollment::query()
            ->where('section_id', $class['section_id'])
            ->where('school_year', $schoolYear)
            ->where('status', 'enrolled')
            ->with('user')
            ->get()
            ->map(fn (Enrollment $enrollment) => $enrollment->user)
            ->filter()
            ->unique('id')
            ->sortBy(fn (User $student) => strtolower($student->last_name.' '.$student->first_name))
            ->values();
    }

    public function classFor(User $teacher, string $schoolYear, string $classKey): ?array
    {
        return $this->classesFor($teacher, $schoolYear)->firstWhere('key', $classKey);
    }

    private function subjectClass(Section $section, Subject $subject): array
    {
        $yearLevel = $section->yearLevel?->name;

        return [
            'key' => 's'.$section->id.'-sub'.$subject->id,
            'kind' => 'subject',
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'section_name' => $section->name,
            'year_level' => $yearLevel,
            'subject_name' => $subject->name,
            'label' => trim(($yearLevel ? $yearLevel.' ' : '').$section->name.' · '.$subject->name),
        ];
    }

    private function advisoryClass(Section $section): array
    {
        $yearLevel = $section->yearLevel?->name;

        return [
            'key' => 's'.$section->id.'-advisory',
            'kind' => 'advisory',
            'section_id' => $section->id,
            'subject_id' => null,
            'section_name' => $section->name,
            'year_level' => $yearLevel,
            'subject_name' => 'Advisory',
            'label' => trim(($yearLevel ? $yearLevel.' ' : '').$section->name.' · Advisory'),
        ];
    }

    private function assignmentMatchesSchoolYear(SectionSubjectTeacher $assignment, string $schoolYear): bool
    {
        $year = $assignment->school_year ?: $assignment->section?->school_year;

        return $year === $schoolYear;
    }
}
