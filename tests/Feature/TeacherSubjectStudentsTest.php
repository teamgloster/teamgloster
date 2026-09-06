<?php

use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function subjectStudentsTeacher(): User
{
    return User::create([
        'first_name' => 'Juan',
        'last_name' => 'Garcia',
        'email' => 'subject-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);
}

function subjectStudentsCatalog(): array
{
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);

    $grade12 = YearLevel::create([
        'name' => 'Grade 12',
        'code' => 'G12-TSS',
        'level_type' => 'senior_high',
        'is_active' => true,
    ]);

    $subject = Subject::create([
        'name' => 'Contemporary Philippine Arts',
        'code' => 'CONARTS12',
        'year_level_id' => $grade12->id,
        'subject_type' => 'core',
        'units' => 1,
        'hours_per_week' => 4,
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    $section = Section::create([
        'name' => 'STEM-A',
        'code' => '12-STEM-A',
        'year_level_id' => $grade12->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $student = User::create([
        'first_name' => 'John Lloyd',
        'last_name' => 'Blanquera',
        'email' => 'blanquera@student.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '555555555555',
        'admission_status' => 'approved',
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $grade12->id,
        'section_id' => $section->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
        'enrolled_at' => now(),
    ]);

    return compact('grade12', 'subject', 'section', 'student');
}

test('teacher can open a subject students page instead of staying on the dashboard', function () {
    $teacher = subjectStudentsTeacher();
    ['subject' => $subject, 'student' => $student] = subjectStudentsCatalog();

    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($teacher)
        ->get("/teacher/subjects/{$subject->id}/students")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Teacher/SubjectStudents')
            ->where('subject.id', $subject->id)
            ->has('students', 1)
            ->where('students.0.last_name', 'Blanquera')
            ->where('students.0.id', $student->id));
});

test('teachers cannot view students for a subject they do not handle', function () {
    $teacher = subjectStudentsTeacher();
    ['subject' => $subject] = subjectStudentsCatalog();

    $this->actingAs($teacher)
        ->get("/teacher/subjects/{$subject->id}/students")
        ->assertForbidden();
});
