<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makePasswordUser(string $role): User
{
    return User::create([
        'first_name' => 'Portal',
        'last_name' => ucfirst($role),
        'email' => $role.'-password@tnhs.test',
        'password' => 'password',
        'role' => $role,
        'lrn' => $role === 'student' ? '123456789099' : null,
        'admission_status' => $role === 'student' ? 'approved' : null,
    ]);
}

test('each portal can open its change password page', function (string $role, string $path) {
    /** @var \Tests\TestCase $this */
    $user = makePasswordUser($role);

    $this->actingAs($user)
        ->get($path)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Account/ChangePassword')
            ->where('role', $role)
        );
})->with([
    'student' => ['student', '/student/password'],
    'teacher' => ['teacher', '/teacher/password'],
    'registrar' => ['registrar', '/registrar/password'],
]);

test('a portal user can replace their password', function (string $role, string $path) {
    /** @var \Tests\TestCase $this */
    $user = makePasswordUser($role);

    $this->actingAs($user)
        ->put($path, [
            'current_password' => 'password',
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Password updated successfully.');

    expect(Hash::check('new-secret', $user->fresh()->password))->toBeTrue();
})->with([
    'student' => ['student', '/student/password'],
    'teacher' => ['teacher', '/teacher/password'],
    'registrar' => ['registrar', '/registrar/password'],
]);

test('the current password must match before it can be changed', function () {
    /** @var \Tests\TestCase $this */
    $student = makePasswordUser('student');

    $this->actingAs($student)
        ->from('/student/password')
        ->put('/student/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
        ])
        ->assertRedirect('/student/password')
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $student->fresh()->password))->toBeTrue();
});

test('a student cannot open another portal password page', function () {
    /** @var \Tests\TestCase $this */
    $student = makePasswordUser('student');

    $this->actingAs($student)
        ->get('/teacher/password')
        ->assertForbidden();
});

test('guests are sent away from the change password page', function () {
    /** @var \Tests\TestCase $this */
    $this->get('/student/password')->assertRedirect('/');
});
