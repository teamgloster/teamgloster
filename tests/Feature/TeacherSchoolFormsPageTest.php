<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('teacher school forms opens the forms page instead of change password', function () {
    /** @var \Tests\TestCase $this */
    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'forms-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher)
        ->get(route('teacher.school-forms'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard/Teacher/SchoolForms'));

    $this->actingAs($teacher)
        ->get(route('teacher.password'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Account/ChangePassword'));

    expect(route('teacher.school-forms', absolute: false))->not->toBe(route('teacher.password', absolute: false));
});
