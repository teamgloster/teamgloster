<?php

use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\User;
use App\Models\YearLevel;
use App\Support\SchoolYear;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('school year normalizes typed variants into one official value', function () {
    expect(SchoolYear::normalize('2025-2026'))->toBe('2025-2026');
    expect(SchoolYear::normalize('SY 2025 - 2026'))->toBe('2025-2026');
    expect(SchoolYear::normalize('2025/2026'))->toBe('2025-2026');
    expect(SchoolYear::normalize('  2025—2026  '))->toBe('2025-2026');
    expect(SchoolYear::normalize('2025'))->toBe('2025-2026');
});

test('school year rejects non-consecutive or invalid years', function () {
    expect(SchoolYear::normalize('2025-2027'))->toBeNull();
    expect(SchoolYear::normalize('abcd'))->toBeNull();
    expect(SchoolYear::normalize(''))->toBeNull();
});

test('school year catalog is unique after messy stored values', function () {
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);

    $level = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-SY',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);

    Section::create([
        'name' => 'Ruby',
        'code' => 'RUBY',
        'year_level_id' => $level->id,
        'school_year' => 'SY 2025 - 2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    Section::create([
        'name' => 'Emerald',
        'code' => 'EMERALD',
        'year_level_id' => $level->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);

    expect(SchoolYear::catalog()->unique()->values()->all())->toContain('2025-2026');
    expect(SchoolYear::catalog()->all())->toBe(SchoolYear::catalog()->unique()->values()->all());
});

test('admin cannot create a duplicate section for the same official school year', function () {
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);

    $admin = User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-sy@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
    $level = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-DUP',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);

    Section::create([
        'name' => 'Ruby',
        'code' => 'RUBY-DUP',
        'year_level_id' => $level->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/sections')
        ->post('/admin/sections', [
            'name' => 'Ruby',
            'code' => 'RUBY-2',
            'year_level_id' => $level->id,
            'school_year' => 'SY 2025 - 2026',
            'capacity' => 40,
            'is_active' => true,
        ])
        ->assertRedirect('/admin/sections')
        ->assertSessionHasErrors('school_year');
});

test('admin settings store the official school year and reject invalid pairs', function () {
    $admin = User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-settings-sy@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);

    $this->actingAs($admin)
        ->from('/admin/settings')
        ->put('/admin/settings', [
            'school_name' => 'Tambo National High School',
            'school_head' => 'Maria Santos',
            'current_school_year' => '2026-2028',
            'current_term' => 1,
            'enrollment_open' => true,
        ])
        ->assertRedirect('/admin/settings')
        ->assertSessionHasErrors('current_school_year');

    $this->actingAs($admin)
        ->from('/admin/settings')
        ->put('/admin/settings', [
            'school_name' => 'Tambo National High School',
            'school_head' => 'Maria Santos',
            'current_school_year' => '2029-2030',
            'current_term' => 1,
            'enrollment_open' => true,
        ])
        ->assertRedirect('/admin/settings')
        ->assertSessionHasErrors('current_school_year');

    AcademicYear::create(['year' => '2029-2030']);

    $this->actingAs($admin)
        ->put('/admin/settings', [
            'school_name' => 'Tambo National High School',
            'school_head' => 'Maria Santos',
            'current_school_year' => '2029-2030',
            'current_term' => 1,
            'enrollment_open' => true,
        ])
        ->assertRedirect();

    expect(SchoolSetting::currentSchoolYear())->toBe('2029-2030');
    expect(SchoolSetting::current()->school_head)->toBe('Maria Santos');
});

test('admin can add a unique school year and cannot add it twice', function () {
    $admin = User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-add-sy@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);

    $this->actingAs($admin)
        ->from('/admin/settings')
        ->post('/admin/settings/school-years', ['start_year' => 2026])
        ->assertRedirect('/admin/settings');

    expect(AcademicYear::query()->where('year', '2026-2027')->exists())->toBeTrue();

    $this->actingAs($admin)
        ->from('/admin/settings')
        ->post('/admin/settings/school-years', ['start_year' => 2026])
        ->assertRedirect('/admin/settings')
        ->assertSessionHasErrors('start_year');
});

test('admin cannot remove the current school year', function () {
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);
    $year = AcademicYear::create(['year' => '2025-2026']);

    $admin = User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-del-sy@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);

    $this->actingAs($admin)
        ->from('/admin/settings')
        ->delete("/admin/settings/school-years/{$year->id}")
        ->assertRedirect('/admin/settings');

    expect(AcademicYear::query()->where('year', '2025-2026')->exists())->toBeTrue();
});
