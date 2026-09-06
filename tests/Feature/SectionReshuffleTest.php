<?php

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\SectionAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function reshuffleStudent(string $first, string $lrn): User
{
    return User::create([
        'first_name' => $first,
        'last_name' => 'Cruz',
        'email' => $lrn.'@reshuffle.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => $lrn,
        'admission_status' => 'approved',
    ]);
}

beforeEach(function () {
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);
});

test('reshuffle groups highest teacher grades together', function () {
    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-RESH',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $subject = Subject::create([
        'name' => 'English 7',
        'code' => 'ENG7RESH',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'units' => 1,
        'hours_per_week' => 4,
        'semester' => 'full_year',
        'is_active' => true,
    ]);
    $sectionA = Section::create([
        'name' => 'Aguila',
        'code' => '7A-R',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 2,
        'is_active' => true,
    ]);
    $sectionB = Section::create([
        'name' => 'Banahaw',
        'code' => '7B-R',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 2,
        'is_active' => true,
    ]);

    $high = reshuffleStudent('High', '200000000001');
    $mid = reshuffleStudent('Mid', '200000000002');
    $low = reshuffleStudent('Low', '200000000003');
    $lowest = reshuffleStudent('Lowest', '200000000004');

    foreach ([$high, $mid, $low, $lowest] as $student) {
        Enrollment::create([
            'user_id' => $student->id,
            'year_level_id' => $grade7->id,
            'section_id' => $sectionB->id,
            'school_year' => '2025-2026',
            'semester' => 'first',
            'status' => 'enrolled',
            'enrollment_type' => 'old',
            'previous_gwa' => 75,
        ]);
    }

    Grade::create(['student_id' => $high->id, 'subject_id' => $subject->id, 'school_year' => '2025-2026', 'final_grade' => 96]);
    Grade::create(['student_id' => $mid->id, 'subject_id' => $subject->id, 'school_year' => '2025-2026', 'final_grade' => 90]);
    Grade::create(['student_id' => $low->id, 'subject_id' => $subject->id, 'school_year' => '2025-2026', 'final_grade' => 80]);
    Grade::create(['student_id' => $lowest->id, 'subject_id' => $subject->id, 'school_year' => '2025-2026', 'final_grade' => 74]);

    $result = app(SectionAssignmentService::class)->reshuffleByTeacherGrades(
        'gwa',
        $grade7->id,
        '2025-2026',
    );

    expect($result['assigned'])->toBe(4);

    $highSection = Enrollment::query()->where('user_id', $high->id)->value('section_id');
    $midSection = Enrollment::query()->where('user_id', $mid->id)->value('section_id');
    $lowSection = Enrollment::query()->where('user_id', $low->id)->value('section_id');
    $lowestSection = Enrollment::query()->where('user_id', $lowest->id)->value('section_id');

    expect($highSection)->toBe($sectionA->id)
        ->and($midSection)->toBe($sectionA->id)
        ->and($lowSection)->toBe($sectionB->id)
        ->and($lowestSection)->toBe($sectionB->id);
});

test('admin can reshuffle sections from the enrollments page', function () {
    $admin = User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'reshuffle-admin@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-RESH2',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    Section::create([
        'name' => 'Rizal',
        'code' => '7R-R',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $student = reshuffleStudent('Jose', '200000000010');
    Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
    ]);
    $subject = Subject::create([
        'name' => 'Filipino 7',
        'code' => 'FIL7RESH',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'units' => 1,
        'hours_per_week' => 4,
        'semester' => 'full_year',
        'is_active' => true,
    ]);
    Grade::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'school_year' => '2025-2026',
        'final_grade' => 88,
    ]);

    $this->actingAs($admin)
        ->post('/admin/enrollments/reshuffle-by-grades', [
            'method' => 'gwa',
            'year_level_id' => $grade7->id,
        ])
        ->assertRedirect();

    expect(Enrollment::query()->where('user_id', $student->id)->value('section_id'))->not->toBeNull();
});
