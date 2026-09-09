<?php

use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSubjectSectionsAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-subject-sections@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

test('admin can assign one or more sections to a subject for its year level', function () {
    SchoolSetting::current()->update(['current_school_year' => '2026-2027']);

    $admin = makeSubjectSectionsAdmin();
    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-SUBSEC',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $sectionA = Section::create([
        'name' => 'Section A',
        'code' => 'G7-A',
        'year_level_id' => $grade7->id,
        'school_year' => '2026-2027',
        'capacity' => 40,
        'is_active' => true,
    ]);
    $sectionB = Section::create([
        'name' => 'Section B',
        'code' => 'G7-B',
        'year_level_id' => $grade7->id,
        'school_year' => '2026-2027',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/subjects')
        ->post('/admin/subjects', [
            'name' => 'Mathematics 7',
            'code' => 'MATH7-SEC',
            'year_level_id' => $grade7->id,
            'subject_type' => 'core',
            'semester' => 'full_year',
            'is_active' => true,
            'section_ids' => [$sectionA->id, $sectionB->id],
        ])
        ->assertRedirect('/admin/subjects');

    $subject = Subject::where('code', 'MATH7-SEC')->first();

    expect($subject)->not->toBeNull()
        ->and($subject->sections()->pluck('sections.id')->sort()->values()->all())
        ->toBe([$sectionA->id, $sectionB->id]);
});

test('a subject cannot be assigned to a section from another year level', function () {
    $admin = makeSubjectSectionsAdmin();
    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-SUBSEC2',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $grade8 = YearLevel::create([
        'name' => 'Grade 8',
        'code' => 'G8-SUBSEC2',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    $grade8Section = Section::create([
        'name' => 'Section A',
        'code' => 'G8-A',
        'year_level_id' => $grade8->id,
        'school_year' => '2026-2027',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/subjects')
        ->post('/admin/subjects', [
            'name' => 'Science 7',
            'code' => 'SCI7-SEC',
            'year_level_id' => $grade7->id,
            'subject_type' => 'core',
            'semester' => 'full_year',
            'is_active' => true,
            'section_ids' => [$grade8Section->id],
        ])
        ->assertRedirect('/admin/subjects')
        ->assertSessionHasErrors('section_ids.0');

    expect(Subject::where('code', 'SCI7-SEC')->exists())->toBeFalse();
});
