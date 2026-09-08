<?php

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\EnrollmentSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function summaryStudent(string $name, string $lrn): User
{
    return User::create([
        'first_name' => $name,
        'last_name' => 'Learner',
        'email' => $lrn.'@summary.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => $lrn,
        'admission_status' => 'approved',
    ]);
}

beforeEach(function () {
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);
});

test('enrollment summary counts promotion dropout completion and graduation', function () {
    $grade7 = YearLevel::create(['name' => 'Grade 7', 'code' => 'G7-SUM', 'level_type' => 'junior_high', 'is_active' => true]);
    $grade8 = YearLevel::create(['name' => 'Grade 8', 'code' => 'G8-SUM', 'level_type' => 'junior_high', 'is_active' => true]);
    $grade10 = YearLevel::create(['name' => 'Grade 10', 'code' => 'G10-SUM', 'level_type' => 'junior_high', 'is_active' => true]);
    $grade11 = YearLevel::create(['name' => 'Grade 11', 'code' => 'G11-SUM', 'level_type' => 'senior_high', 'is_active' => true]);
    $grade12 = YearLevel::create(['name' => 'Grade 12', 'code' => 'G12-SUM', 'level_type' => 'senior_high', 'is_active' => true]);

    $math7 = Subject::create([
        'name' => 'Math 7',
        'code' => 'MATH7SUM',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);
    $math10 = Subject::create([
        'name' => 'Math 10',
        'code' => 'MATH10SUM',
        'year_level_id' => $grade10->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);
    $math12 = Subject::create([
        'name' => 'Math 12',
        'code' => 'MATH12SUM',
        'year_level_id' => $grade12->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    $promoted = summaryStudent('Promoted', '100000000001');
    $dropped = summaryStudent('Dropped', '100000000002');
    $completer = summaryStudent('Completer', '100000000003');
    $graduate = summaryStudent('Graduate', '100000000004');

    Enrollment::create([
        'user_id' => $promoted->id,
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
    ]);
    Enrollment::create([
        'user_id' => $promoted->id,
        'year_level_id' => $grade8->id,
        'school_year' => '2026-2027',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
    ]);

    Enrollment::create([
        'user_id' => $dropped->id,
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'dropped',
        'enrollment_type' => 'old',
    ]);

    Enrollment::create([
        'user_id' => $completer->id,
        'year_level_id' => $grade10->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
    ]);
    Grade::create([
        'student_id' => $completer->id,
        'subject_id' => $math10->id,
        'school_year' => '2025-2026',
        'final_grade' => 86,
    ]);

    Enrollment::create([
        'user_id' => $graduate->id,
        'year_level_id' => $grade12->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
    ]);
    Grade::create([
        'student_id' => $graduate->id,
        'subject_id' => $math12->id,
        'school_year' => '2025-2026',
        'final_grade' => 90,
    ]);

    $summary = app(EnrollmentSummaryService::class)->summarize('2025-2026');

    expect($summary['totals']['enrolled'])->toBe(3)
        ->and($summary['totals']['dropped'])->toBe(1)
        ->and($summary['totals']['promoted'])->toBe(2)
        ->and($summary['totals']['completed'])->toBe(1)
        ->and($summary['totals']['graduated'])->toBe(1)
        ->and($summary['totals']['dropout_rate'])->toBe(25.0)
        ->and($summary['totals']['completion_rate'])->toBe(100.0)
        ->and($summary['totals']['graduation_rate'])->toBe(100.0);

    $admin = User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'summary-admin@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);

    $this->actingAs($admin)
        ->get('/admin/enrollment-summary?school_year=2025-2026')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/EnrollmentSummary/Index')
            ->where('summary.totals.dropped', 1)
            ->where('summary.totals.graduated', 1)
        );
});

test('admin can mark an enrolled student as a drop-out', function () {
    $admin = User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'drop-admin@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
    $student = summaryStudent('Leaver', '100000000099');
    $grade7 = YearLevel::create(['name' => 'Grade 7', 'code' => 'G7-DROP', 'level_type' => 'junior_high', 'is_active' => true]);
    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
    ]);

    $this->actingAs($admin)
        ->post("/admin/enrollments/{$enrollment->id}/drop", [
            'remarks' => 'Transferred out',
        ])
        ->assertRedirect();

    expect($enrollment->fresh()->status)->toBe('dropped');
});
