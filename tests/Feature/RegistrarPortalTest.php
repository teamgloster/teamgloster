<?php

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeRegistrar(): User
{
    return User::create([
        'first_name' => 'School',
        'last_name' => 'Registrar',
        'email' => 'registrar@tnhs.test',
        'password' => 'password',
        'role' => 'registrar',
    ]);
}

function makePortalAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-portal@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

function seedRegistrarCatalog(): array
{
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);

    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $grade8 = YearLevel::create([
        'name' => 'Grade 8',
        'code' => 'G8',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $section = Section::create([
        'name' => 'Rizal',
        'code' => '7-R',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $math = Subject::create([
        'name' => 'Mathematics 7',
        'code' => 'MATH7R',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    return compact('grade7', 'grade8', 'section', 'math');
}

test('registrar can sign in and reach the registrar portal', function () {
    $registrar = makeRegistrar();

    $this->post('/login', [
        'email' => $registrar->email,
        'password' => 'password',
        'role' => 'registrar',
    ])->assertRedirect('/dashboard/registrar');
});

test('registrar can view year levels sections and grading records', function () {
    $registrar = makeRegistrar();
    ['grade7' => $grade7, 'section' => $section, 'math' => $math] = seedRegistrarCatalog();

    $student = User::create([
        'first_name' => 'Ana',
        'last_name' => 'Reyes',
        'email' => 'ana@student.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '333333333333',
        'admission_status' => 'approved',
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $grade7->id,
        'section_id' => $section->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
        'enrolled_at' => now(),
    ]);

    Grade::create([
        'student_id' => $student->id,
        'subject_id' => $math->id,
        'section_id' => $section->id,
        'school_year' => '2025-2026',
        'final_grade' => 88,
    ]);

    $this->actingAs($registrar)
        ->get('/registrar')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard/Registrar/Index'));

    $this->actingAs($registrar)
        ->get('/registrar/year-levels')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Registrar/YearLevels')
            ->has('yearLevels', 2));

    $this->actingAs($registrar)
        ->get('/registrar/sections')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Registrar/Sections')
            ->has('sections', 1));

    $this->actingAs($registrar)
        ->get('/registrar/grades')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Registrar/Grades')
            ->has('records', 1)
            ->where('records.0.remarks', 'Passed'));

    $this->actingAs($registrar)
        ->get('/registrar/students')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Registrar/Students')
            ->has('sections', 1)
            ->where('sections.0.name', 'Rizal')
            ->where('sections.0.enrolled_count', 1)
            ->where('selectedSchoolYear', '2025-2026')
            ->has('yearLevels', 2));

    $this->actingAs($registrar)
        ->get("/registrar/sections/{$section->id}/enrollment")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Registrar/SectionEnrollment')
            ->where('section.id', $section->id)
            ->has('students', 1)
            ->where('students.0.last_name', 'Reyes')
            ->where('students.0.current_enrollment.status', 'enrolled'));

    $this->actingAs($registrar)
        ->get("/registrar/students/{$student->id}/enrollment")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Registrar/EnrollmentDetails')
            ->where('student.id', $student->id)
            ->where('currentEnrollment.status', 'enrolled')
            ->where('currentEnrollment.section.name', 'Rizal')
            ->has('enrollments', 1));

    $this->actingAs($registrar)
        ->get("/registrar/students/{$student->id}/sf9")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/SchoolForms/Sf9')
            ->where('viewer', 'registrar'));

    $this->actingAs($registrar)
        ->get("/registrar/students/{$student->id}/sf10")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/SchoolForms/Sf10')
            ->where('viewer', 'registrar'));
});

test('registrar can promote a student who passed', function () {
    $registrar = makeRegistrar();
    ['grade7' => $grade7, 'grade8' => $grade8, 'math' => $math] = seedRegistrarCatalog();

    $english = Subject::create([
        'name' => 'English 7',
        'code' => 'ENG7R',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    $student = User::create([
        'first_name' => 'Pedro',
        'last_name' => 'Santos',
        'email' => 'pedro@student.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '444444444444',
        'admission_status' => 'approved',
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
        'enrolled_at' => now(),
    ]);

    Grade::create([
        'student_id' => $student->id,
        'subject_id' => $math->id,
        'school_year' => '2025-2026',
        'final_grade' => 90,
    ]);
    Grade::create([
        'student_id' => $student->id,
        'subject_id' => $english->id,
        'school_year' => '2025-2026',
        'final_grade' => 86,
    ]);

    $this->actingAs($registrar)
        ->from('/registrar/students')
        ->post("/registrar/students/{$student->id}/promote")
        ->assertRedirect('/registrar/students');

    expect(Enrollment::where('user_id', $student->id)->value('year_level_id'))->toBe($grade8->id);
});

test('administrators and teachers cannot open the registrar portal', function () {
    $admin = makePortalAdmin();
    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Cruz',
        'email' => 'teacher-portal@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($admin)->get('/registrar')->assertForbidden();
    $this->actingAs($teacher)->get('/registrar/grades')->assertForbidden();
});
