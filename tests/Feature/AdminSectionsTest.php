<?php

use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSectionsAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-sections@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

function makeYearLevel(string $name = 'Grade 7', string $code = 'G7'): YearLevel
{
    return YearLevel::create([
        'name' => $name,
        'code' => $code,
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
}

test('admin sections page defaults to the school year that has sections', function () {
    SchoolSetting::current()->update(['current_school_year' => '2026-2027']);

    $admin = makeSectionsAdmin();
    $grade7 = makeYearLevel();

    Section::create([
        'name' => 'Ruby',
        'code' => 'G7-RUBY',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    Section::create([
        'name' => 'Emerald',
        'code' => 'G7-EMERALD',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    Section::create([
        'name' => 'hahah',
        'code' => 'haha123',
        'year_level_id' => $grade7->id,
        'school_year' => '2026-2027',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/sections')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin/Sections')
            ->where('selectedSchoolYear', '2025-2026')
            ->has('sections', 2)
            ->where('sections.0.name', 'Emerald')
            ->where('sections.1.name', 'Ruby')
        );
});

test('admin can view sections for a selected school year', function () {
    SchoolSetting::current()->update(['current_school_year' => '2026-2027']);

    $admin = makeSectionsAdmin();
    $grade7 = makeYearLevel();

    Section::create([
        'name' => 'Ruby',
        'code' => 'G7-RUBY',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);
    Section::create([
        'name' => 'hahah',
        'code' => 'haha123',
        'year_level_id' => $grade7->id,
        'school_year' => '2026-2027',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/sections?sy=2026-2027')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin/Sections')
            ->where('selectedSchoolYear', '2026-2027')
            ->has('sections', 1)
            ->where('sections.0.name', 'hahah')
        );
});
