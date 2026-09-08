<?php

use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAssignmentsAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-assignments@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

function makeAssignmentsTeacher(string $email, string $first, string $last): User
{
    return User::create([
        'first_name' => $first,
        'last_name' => $last,
        'email' => $email,
        'password' => 'password',
        'role' => 'teacher',
    ]);
}

function makeAssignmentsSubject(): Subject
{
    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-TS',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);

    return Subject::create([
        'name' => 'Mathematics 7',
        'code' => 'MATH7-TS',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);
}

test('a subject cannot be assigned to a second teacher', function () {
    $admin = makeAssignmentsAdmin();
    $firstTeacher = makeAssignmentsTeacher('torres@tnhs.test', 'Pedro', 'Torres');
    $secondTeacher = makeAssignmentsTeacher('hernandez@tnhs.test', 'Patricia', 'Hernandez');
    $subject = makeAssignmentsSubject();

    TeacherSubject::create([
        'teacher_id' => $firstTeacher->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($admin)
        ->from('/admin/teacher-assignments')
        ->post('/admin/teacher-subjects/bulk', [
            'teacher_id' => $secondTeacher->id,
            'subject_ids' => [$subject->id],
        ])
        ->assertRedirect('/admin/teacher-assignments')
        ->assertSessionHasErrors('subject_ids');

    expect(TeacherSubject::where('subject_id', $subject->id)->count())->toBe(1)
        ->and(TeacherSubject::where('teacher_id', $firstTeacher->id)->where('subject_id', $subject->id)->exists())->toBeTrue()
        ->and(TeacherSubject::where('teacher_id', $secondTeacher->id)->where('subject_id', $subject->id)->exists())->toBeFalse();
});

test('a teacher can keep a subject that is already assigned to them', function () {
    $admin = makeAssignmentsAdmin();
    $teacher = makeAssignmentsTeacher('garcia@tnhs.test', 'Juan', 'Garcia');
    $subject = makeAssignmentsSubject();

    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($admin)
        ->from('/admin/teacher-assignments')
        ->post('/admin/teacher-subjects/bulk', [
            'teacher_id' => $teacher->id,
            'subject_ids' => [$subject->id],
        ])
        ->assertRedirect('/admin/teacher-assignments')
        ->assertSessionHasNoErrors();

    expect(TeacherSubject::where('teacher_id', $teacher->id)->where('subject_id', $subject->id)->exists())->toBeTrue();
});
