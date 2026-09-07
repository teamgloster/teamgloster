<?php

use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeEnrollmentAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-enrollments@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

function makeEnrollmentStudent(): User
{
    return User::create([
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan.delacruz@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '123456789012',
        'admission_status' => 'approved',
    ]);
}

beforeEach(function () {
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);
});

test('admin cannot assign a section again after the student already has one', function () {
    $admin = makeEnrollmentAdmin();
    $student = makeEnrollmentStudent();
    $yearLevel = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-SEC',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $sectionA = Section::create([
        'name' => 'Ruby',
        'code' => 'G7-RUBY',
        'year_level_id' => $yearLevel->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $sectionB = Section::create([
        'name' => 'Emerald',
        'code' => 'G7-EMERALD',
        'year_level_id' => $yearLevel->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $sectionA->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'new',
    ]);

    $this->actingAs($admin)
        ->from('/admin/enrollments')
        ->post("/admin/enrollments/{$enrollment->id}/assign-section", [
            'section_id' => $sectionB->id,
        ])
        ->assertRedirect('/admin/enrollments')
        ->assertSessionHasErrors('section_id');

    expect($enrollment->fresh()->section_id)->toBe($sectionA->id);
});

test('admin must confirm before transferring a student to another section', function () {
    $admin = makeEnrollmentAdmin();
    $student = makeEnrollmentStudent();
    $yearLevel = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-SEC2',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $sectionA = Section::create([
        'name' => 'Ruby',
        'code' => 'G7-RUBY2',
        'year_level_id' => $yearLevel->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $sectionB = Section::create([
        'name' => 'Emerald',
        'code' => 'G7-EMERALD2',
        'year_level_id' => $yearLevel->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $sectionA->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'new',
    ]);

    $this->actingAs($admin)
        ->from('/admin/enrollments')
        ->post("/admin/enrollments/{$enrollment->id}/transfer-section", [
            'section_id' => $sectionB->id,
        ])
        ->assertRedirect('/admin/enrollments')
        ->assertSessionHasErrors('confirm_transfer');

    expect($enrollment->fresh()->section_id)->toBe($sectionA->id);
});

test('admin can transfer a confirmed student to another section', function () {
    $admin = makeEnrollmentAdmin();
    $student = makeEnrollmentStudent();
    $yearLevel = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-SEC3',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $sectionA = Section::create([
        'name' => 'Ruby',
        'code' => 'G7-RUBY3',
        'year_level_id' => $yearLevel->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $sectionB = Section::create([
        'name' => 'Emerald',
        'code' => 'G7-EMERALD3',
        'year_level_id' => $yearLevel->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $sectionA->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'new',
    ]);

    $this->actingAs($admin)
        ->from('/admin/enrollments')
        ->post("/admin/enrollments/{$enrollment->id}/transfer-section", [
            'section_id' => $sectionB->id,
            'confirm_transfer' => true,
        ])
        ->assertRedirect('/admin/enrollments')
        ->assertSessionHas('success');

    expect($enrollment->fresh()->section_id)->toBe($sectionB->id);
});
