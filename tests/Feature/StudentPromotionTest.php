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

function createAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

function createStudent(string $lrn = '123456789012'): User
{
    return User::create([
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => $lrn.'@student.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => $lrn,
        'admission_status' => 'approved',
    ]);
}

/**
 * @return array{grade7: YearLevel, grade8: YearLevel, grade12: YearLevel, math: Subject, english: Subject}
 */
function createYearLevelsAndSubjects(): array
{
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
    $grade12 = YearLevel::create([
        'name' => 'Grade 12',
        'code' => 'G12',
        'level_type' => 'senior_high',
        'is_active' => true,
    ]);

    $math = Subject::create([
        'name' => 'Mathematics 7',
        'code' => 'MATH7',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);
    $english = Subject::create([
        'name' => 'English 7',
        'code' => 'ENG7',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    return compact('grade7', 'grade8', 'grade12', 'math', 'english');
}

function enrollStudent(User $student, YearLevel $yearLevel, string $schoolYear, string $status = 'enrolled'): Enrollment
{
    return Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $yearLevel->id,
        'school_year' => $schoolYear,
        'semester' => 'first',
        'status' => $status,
        'enrollment_type' => 'old',
        'enrolled_at' => $status === 'enrolled' ? now() : null,
    ]);
}

function giveFinalGrades(User $student, array $subjectGrades, string $schoolYear): void
{
    foreach ($subjectGrades as $subjectId => $finalGrade) {
        Grade::create([
            'student_id' => $student->id,
            'subject_id' => $subjectId,
            'school_year' => $schoolYear,
            'final_grade' => $finalGrade,
        ]);
    }
}

beforeEach(function () {
    SchoolSetting::current()->update([
        'current_school_year' => '2025-2026',
    ]);
});

test('admin can promote a student who passed all subjects', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7, 'grade8' => $grade8, 'math' => $math, 'english' => $english] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade7, '2025-2026');
    giveFinalGrades($student, [
        $math->id => 88,
        $english->id => 90,
    ], '2025-2026');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->post("/admin/students/{$student->id}/promote")
        ->assertRedirect('/admin/students');

    $enrollment = Enrollment::where('user_id', $student->id)
        ->where('school_year', '2025-2026')
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->year_level_id)->toBe($grade8->id)
        ->and($enrollment->section_id)->toBeNull();
});

test('admin cannot promote a student who failed a subject', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7, 'grade8' => $grade8, 'math' => $math, 'english' => $english] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade7, '2025-2026');
    giveFinalGrades($student, [
        $math->id => 74,
        $english->id => 90,
    ], '2025-2026');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->post("/admin/students/{$student->id}/promote")
        ->assertRedirect('/admin/students')
        ->assertSessionHasErrors('promotion');

    expect(Enrollment::where('user_id', $student->id)->value('year_level_id'))->toBe($grade7->id);
});

test('admin cannot promote a student with incomplete final grades', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7, 'math' => $math] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade7, '2025-2026');
    giveFinalGrades($student, [
        $math->id => 88,
    ], '2025-2026');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->post("/admin/students/{$student->id}/promote")
        ->assertSessionHasErrors('promotion');
});

test('admin can change a passing student to the next year level', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7, 'grade8' => $grade8, 'math' => $math, 'english' => $english] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade7, '2025-2026');
    giveFinalGrades($student, [
        $math->id => 80,
        $english->id => 81,
    ], '2025-2026');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->put("/admin/students/{$student->id}/year-level", [
            'year_level_id' => $grade8->id,
            'remarks' => 'Passed Grade 7',
        ])
        ->assertRedirect('/admin/students');

    expect(Enrollment::where('user_id', $student->id)->value('year_level_id'))->toBe($grade8->id);
    expect($student->fresh()->year_level_applying)->toBe('Grade 8');
});

test('admin cannot change a failing student to a higher year level', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7, 'grade8' => $grade8, 'math' => $math, 'english' => $english] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade7, '2025-2026');
    giveFinalGrades($student, [
        $math->id => 70,
        $english->id => 72,
    ], '2025-2026');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->put("/admin/students/{$student->id}/year-level", [
            'year_level_id' => $grade8->id,
        ])
        ->assertSessionHasErrors('promotion');

    expect(Enrollment::where('user_id', $student->id)->value('year_level_id'))->toBe($grade7->id);
});

test('admin cannot demote a student to a lower year level', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7, 'grade8' => $grade8, 'math' => $math, 'english' => $english] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade8, '2025-2026');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->put("/admin/students/{$student->id}/year-level", [
            'year_level_id' => $grade7->id,
        ])
        ->assertSessionHasErrors('promotion');
});

test('admin can create a current-year enrollment when a student passed last year', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7, 'grade8' => $grade8, 'math' => $math, 'english' => $english] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade7, '2024-2025');
    giveFinalGrades($student, [
        $math->id => 85,
        $english->id => 86,
    ], '2024-2025');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->post("/admin/students/{$student->id}/promote")
        ->assertRedirect('/admin/students');

    $current = Enrollment::where('user_id', $student->id)
        ->where('school_year', '2025-2026')
        ->first();

    expect($current)->not->toBeNull()
        ->and($current->year_level_id)->toBe($grade8->id)
        ->and($current->status)->toBe('approved');
});

test('admin cannot promote a grade 12 student who already passed', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade12' => $grade12] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade12, '2025-2026');

    Subject::create([
        'name' => 'Research',
        'code' => 'RES12',
        'year_level_id' => $grade12->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    $research = Subject::where('code', 'RES12')->first();
    giveFinalGrades($student, [$research->id => 90], '2025-2026');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->post("/admin/students/{$student->id}/promote")
        ->assertSessionHasErrors('promotion');
});

test('admin can bulk promote only eligible students', function () {
    $admin = createAdmin();
    $passing = createStudent('111111111111');
    $failing = createStudent('222222222222');
    ['grade7' => $grade7, 'grade8' => $grade8, 'math' => $math, 'english' => $english] = createYearLevelsAndSubjects();

    enrollStudent($passing, $grade7, '2025-2026');
    enrollStudent($failing, $grade7, '2025-2026');

    giveFinalGrades($passing, [
        $math->id => 90,
        $english->id => 91,
    ], '2025-2026');
    giveFinalGrades($failing, [
        $math->id => 70,
        $english->id => 71,
    ], '2025-2026');

    $this->actingAs($admin)
        ->from('/admin/students')
        ->post('/admin/students/promote-selected', [
            'student_ids' => [$passing->id, $failing->id],
        ])
        ->assertRedirect('/admin/students');

    expect(Enrollment::where('user_id', $passing->id)->value('year_level_id'))->toBe($grade8->id);
    expect(Enrollment::where('user_id', $failing->id)->value('year_level_id'))->toBe($grade7->id);
});

test('admin students page shows year level and section from latest enrollment', function () {
    SchoolSetting::current()->update([
        'current_school_year' => '2026-2027',
    ]);

    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7] = createYearLevelsAndSubjects();

    $section = Section::create([
        'name' => 'Ruby',
        'code' => 'G7-RUBY',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $enrollment = enrollStudent($student, $grade7, '2025-2026');
    $enrollment->update(['section_id' => $section->id]);

    $this->actingAs($admin)
        ->get('/admin/students')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin/Students')
            ->where('students.0.current_enrollment.year_level.name', 'Grade 7')
            ->where('students.0.current_enrollment.section.name', 'Ruby')
        );
});

test('admin students page includes promotion summaries', function () {
    $admin = createAdmin();
    $student = createStudent();
    ['grade7' => $grade7, 'math' => $math, 'english' => $english] = createYearLevelsAndSubjects();

    enrollStudent($student, $grade7, '2025-2026');
    giveFinalGrades($student, [
        $math->id => 88,
        $english->id => 90,
    ], '2025-2026');

    $this->actingAs($admin)
        ->get('/admin/students')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin/Students')
            ->has('students.0.promotion')
            ->where('students.0.promotion.can_promote', true)
            ->where('students.0.promotion.status', 'eligible')
        );
});

test('teachers cannot promote students', function () {
    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);
    $student = createStudent();
    ['grade7' => $grade7] = createYearLevelsAndSubjects();
    enrollStudent($student, $grade7, '2025-2026');

    $this->actingAs($teacher)
        ->post("/admin/students/{$student->id}/promote")
        ->assertForbidden();
});
