<?php

use App\Models\SchoolSetting;
use App\Models\Strand;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeApprovedShsStudent(): User
{
    return User::create([
        'first_name' => 'Maria',
        'last_name' => 'Reyes',
        'email' => 'maria.reyes@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '123456789014',
        'admission_status' => 'approved',
        'preferred_strand' => 'STEM',
    ]);
}

beforeEach(function () {
    SchoolSetting::current()->update([
        'current_school_year' => '2026-2027',
        'enrollment_open' => true,
    ]);
});

test('student enrollment page shows academic tracks', function () {
    $student = makeApprovedShsStudent();

    $this->actingAs($student)
        ->get('/student/enrollment')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Student/Enrollment')
            ->has('strands')
            ->where('enrollment.academicTrackLabel', Strand::labelFor('STEM'))
            ->where('user.preferred_strand', 'STEM')
        );
});

test('student dashboard shows the academic track', function () {
    $student = makeApprovedShsStudent();

    $this->actingAs($student)
        ->get('/student')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Student/Index')
            ->where('enrollment.academicTrackLabel', Strand::labelFor('STEM'))
        );
});

test('senior high enrollment requires an academic track', function () {
    $student = makeApprovedShsStudent();
    $grade11 = YearLevel::create([
        'name' => 'Grade 11',
        'code' => 'G11-ENR',
        'level_type' => 'senior_high',
        'is_active' => true,
    ]);

    $this->actingAs($student)
        ->from('/student/enrollment')
        ->post('/enrollment/submit', [
            'year_level_id' => $grade11->id,
            'enrollment_type' => 'new',
            'previous_school' => 'TNHS',
        ])
        ->assertRedirect('/student/enrollment')
        ->assertSessionHasErrors('preferred_strand');
});

test('student can save an academic track when enrolling in senior high', function () {
    $student = makeApprovedShsStudent();
    $grade11 = YearLevel::create([
        'name' => 'Grade 11',
        'code' => 'G11-SAVE',
        'level_type' => 'senior_high',
        'is_active' => true,
    ]);

    $this->actingAs($student)
        ->from('/student/enrollment')
        ->post('/enrollment/submit', [
            'year_level_id' => $grade11->id,
            'enrollment_type' => 'new',
            'previous_school' => 'TNHS',
            'preferred_strand' => 'HUMSS',
        ])
        ->assertRedirect('/student/enrollment');

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $student->id,
        'year_level_id' => $grade11->id,
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $student->id,
        'preferred_strand' => 'HUMSS',
    ]);
});
