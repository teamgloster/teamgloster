<?php

use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\SectionSubjectTeacher;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function gradesTeacher(): User
{
    return User::create([
        'first_name' => 'Juan',
        'last_name' => 'Garcia',
        'email' => 'grades-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);
}

function gradesCatalog(): array
{
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);

    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-TG',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $grade12 = YearLevel::create([
        'name' => 'Grade 12',
        'code' => 'G12-TG',
        'level_type' => 'senior_high',
        'is_active' => true,
    ]);

    $math = Subject::create([
        'name' => 'Mathematics 7',
        'code' => 'MATH7-TG',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);
    $arts = Subject::create([
        'name' => 'Contemporary Philippine Arts',
        'code' => 'CONARTS-TG',
        'year_level_id' => $grade12->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    $rosal = Section::create([
        'name' => 'Rosal',
        'code' => 'G7-ROSAL',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $gawgaw = Section::create([
        'name' => 'gawgaw',
        'code' => 'G12-GAW',
        'year_level_id' => $grade12->id,
        'school_year' => '2027-2028',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $currentStudent = User::create([
        'first_name' => 'Elena',
        'last_name' => 'Aguilar',
        'email' => 'aguilar@student.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '100000000001',
        'admission_status' => 'approved',
    ]);
    $futureStudent = User::create([
        'first_name' => 'John Lloyd',
        'last_name' => 'Blanquera',
        'email' => 'blanquera-grades@student.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '100000000002',
        'admission_status' => 'approved',
    ]);

    Enrollment::create([
        'user_id' => $currentStudent->id,
        'year_level_id' => $grade7->id,
        'section_id' => $rosal->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
        'enrolled_at' => now(),
    ]);
    Enrollment::create([
        'user_id' => $futureStudent->id,
        'year_level_id' => $grade12->id,
        'section_id' => $gawgaw->id,
        'school_year' => '2027-2028',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
        'enrolled_at' => now(),
    ]);

    return compact('grade7', 'grade12', 'math', 'arts', 'rosal', 'gawgaw', 'currentStudent', 'futureStudent');
}

test('teacher grades only include students from current school year assignments', function () {
    $teacher = gradesTeacher();
    ['math' => $math, 'arts' => $arts, 'rosal' => $rosal, 'gawgaw' => $gawgaw, 'currentStudent' => $currentStudent] = gradesCatalog();

    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
    ]);
    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $arts->id,
    ]);

    // Assignment for the CURRENT SY — the only students that should show.
    SectionSubjectTeacher::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
        'section_id' => $rosal->id,
        'school_year' => '2025-2026',
        'is_active' => true,
    ]);

    // Stale assignment in a different SY — must NOT bleed students into
    // the current-SY grade list.
    SectionSubjectTeacher::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $arts->id,
        'section_id' => $gawgaw->id,
        'school_year' => '2027-2028',
        'is_active' => true,
    ]);

    $this->actingAs($teacher)
        ->get('/dashboard/teacher')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Teacher')
            ->has('studentGrades', 1)
            ->where('studentGrades.0.student.id', $currentStudent->id)
            ->where('studentGrades.0.subject.id', $math->id)
            ->where('studentGrades.0.section.name', 'Rosal')
            ->has('teacherSubjects', 2)
        );
});

test('teacher assigned a subject sees current school year students for that year level', function () {
    $teacher = gradesTeacher();
    ['math' => $math, 'arts' => $arts, 'gawgaw' => $gawgaw, 'currentStudent' => $currentStudent] = gradesCatalog();

    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
    ]);
    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $arts->id,
    ]);

    // Only a future-year section assignment — current SY students still
    // come from the Teacher-Subject year level roster.
    SectionSubjectTeacher::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $arts->id,
        'section_id' => $gawgaw->id,
        'school_year' => '2027-2028',
        'is_active' => true,
    ]);

    $this->actingAs($teacher)
        ->get('/dashboard/teacher')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Teacher')
            ->has('studentGrades', 1)
            ->where('studentGrades.0.student.id', $currentStudent->id)
            ->where('studentGrades.0.subject.id', $math->id)
            ->has('teacherSubjects', 2)
        );
});

test('brand-new teacher with no assignments still sees enrolled students in the current school year via teachable subjects', function () {
    $teacher = gradesTeacher();
    ['math' => $math, 'currentStudent' => $currentStudent] = gradesCatalog();

    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
    ]);

    $this->actingAs($teacher)
        ->get('/dashboard/teacher')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Teacher')
            ->has('studentGrades', 1)
            ->where('studentGrades.0.student.id', $currentStudent->id)
            ->where('studentGrades.0.subject.id', $math->id)
            ->has('teacherSubjects', 1)
            ->where('teacherSubjects.0.id', $math->id)
        );
});

test('assigned subjects appear on the teacher dashboard even without enrolled students', function () {
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);

    $teacher = gradesTeacher();
    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-EMPTY',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $math = Subject::create([
        'name' => 'Mathematics 7',
        'code' => 'MATH7-EMPTY',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
    ]);

    $this->actingAs($teacher)
        ->get('/dashboard/teacher')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Teacher')
            ->has('teacherSubjects', 1)
            ->where('teacherSubjects.0.id', $math->id)
            ->where('teacherSubjects.0.name', 'Mathematics 7')
            ->has('studentGrades', 0)
        );
});
