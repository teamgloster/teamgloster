<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAccountsAdmin(string $email = 'admin-accounts@tnhs.test'): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => $email,
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

test('admin can open the accounts page', function () {
    $admin = makeAccountsAdmin();

    $this->actingAs($admin)
        ->get('/admin/accounts')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin/Accounts')
            ->has('accounts', 1)
            ->where('accounts.0.email', $admin->email)
            ->where('accounts.0.role', 'administrator')
        );
});

test('admin can create a registrar account', function () {
    $admin = makeAccountsAdmin();

    $this->actingAs($admin)
        ->from('/admin/accounts')
        ->post('/admin/accounts', [
            'first_name' => 'School',
            'last_name' => 'Registrar',
            'email' => 'registrar@tnhs.test',
            'role' => 'registrar',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect('/admin/accounts');

    $this->assertDatabaseHas('users', [
        'email' => 'registrar@tnhs.test',
        'role' => 'registrar',
        'first_name' => 'School',
        'last_name' => 'Registrar',
    ]);

    $this->post('/logout');

    $this->post('/login', [
        'email' => 'registrar@tnhs.test',
        'password' => 'secret123',
        'role' => 'registrar',
    ])->assertRedirect('/dashboard/registrar');
});

test('admin can create another administrator account', function () {
    $admin = makeAccountsAdmin();

    $this->actingAs($admin)
        ->from('/admin/accounts')
        ->post('/admin/accounts', [
            'first_name' => 'Second',
            'last_name' => 'Admin',
            'email' => 'second-admin@tnhs.test',
            'role' => 'administrator',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect('/admin/accounts');

    $this->assertDatabaseHas('users', [
        'email' => 'second-admin@tnhs.test',
        'role' => 'administrator',
    ]);
});

test('admin cannot create a teacher from the accounts page', function () {
    $admin = makeAccountsAdmin();

    $this->actingAs($admin)
        ->from('/admin/accounts')
        ->post('/admin/accounts', [
            'first_name' => 'New',
            'last_name' => 'Teacher',
            'email' => 'teacher-account@tnhs.test',
            'role' => 'teacher',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect('/admin/accounts')
        ->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', [
        'email' => 'teacher-account@tnhs.test',
    ]);
});

test('admin cannot delete their own account', function () {
    $admin = makeAccountsAdmin();

    $this->actingAs($admin)
        ->from('/admin/accounts')
        ->delete("/admin/accounts/{$admin->id}")
        ->assertRedirect('/admin/accounts')
        ->assertSessionHasErrors('account');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'role' => 'administrator',
    ]);
});

test('admin can delete a registrar account', function () {
    $admin = makeAccountsAdmin();
    $registrar = User::create([
        'first_name' => 'School',
        'last_name' => 'Registrar',
        'email' => 'registrar-delete@tnhs.test',
        'password' => 'password',
        'role' => 'registrar',
    ]);

    $this->actingAs($admin)
        ->from('/admin/accounts')
        ->delete("/admin/accounts/{$registrar->id}")
        ->assertRedirect('/admin/accounts');

    $this->assertDatabaseMissing('users', [
        'id' => $registrar->id,
    ]);
});

test('registrars and teachers cannot open the accounts page', function () {
    $registrar = User::create([
        'first_name' => 'School',
        'last_name' => 'Registrar',
        'email' => 'registrar-blocked@tnhs.test',
        'password' => 'password',
        'role' => 'registrar',
    ]);
    $teacher = User::create([
        'first_name' => 'Subject',
        'last_name' => 'Teacher',
        'email' => 'teacher-blocked@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($registrar)->get('/admin/accounts')->assertForbidden();
    $this->actingAs($teacher)->get('/admin/accounts')->assertForbidden();
});
