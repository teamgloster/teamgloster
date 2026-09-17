<?php

use App\Models\Strand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeStrandsAdmin(string $email = 'admin-strands@tnhs.test'): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => $email,
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

test('admin can open the strands page', function () {
    $admin = makeStrandsAdmin();

    $this->actingAs($admin)
        ->get('/admin/strands')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin/Strands')
            ->has('strands', 5)
            ->where('strands.0.code', 'ABM')
        );
});

test('admin can create a strand', function () {
    $admin = makeStrandsAdmin();

    $this->actingAs($admin)
        ->from('/admin/strands')
        ->post('/admin/strands', [
            'name' => 'Information and Communications Technology',
            'code' => 'ict',
            'description' => 'TVL ICT specialization',
            'is_active' => true,
        ])
        ->assertRedirect('/admin/strands');

    $this->assertDatabaseHas('strands', [
        'name' => 'Information and Communications Technology',
        'code' => 'ICT',
        'is_active' => true,
    ]);
});

test('admin can update a strand', function () {
    $admin = makeStrandsAdmin();
    $strand = Strand::query()->where('code', 'STEM')->firstOrFail();

    $this->actingAs($admin)
        ->from('/admin/strands')
        ->put("/admin/strands/{$strand->id}", [
            'name' => 'STEM Honors',
            'code' => 'STEM',
            'description' => 'Updated description',
            'is_active' => false,
        ])
        ->assertRedirect('/admin/strands');

    $this->assertDatabaseHas('strands', [
        'id' => $strand->id,
        'name' => 'STEM Honors',
        'code' => 'STEM',
        'is_active' => false,
    ]);
});

test('inactive strands are hidden from the admission form', function () {
    Strand::query()->where('code', 'GAS')->update(['is_active' => false]);

    $this->get('/admission/apply')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Auth/register')
            ->has('strands', 4)
            ->where('strands.0.code', 'ABM')
            ->where('strands.1.code', 'HUMSS')
            ->where('strands.2.code', 'STEM')
            ->where('strands.3.code', 'TVL')
        );
});

test('admission form lists active strands created by admin', function () {
    Strand::query()->create([
        'name' => 'Home Economics',
        'code' => 'HE',
        'description' => 'TVL Home Economics',
        'is_active' => true,
    ]);

    $this->get('/admission/apply')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Auth/register')
            ->has('strands', 6)
            ->where('strands.2.code', 'HE')
        );
});

test('registration accepts an active strand from the admission form', function () {
    $this->from('/admission/apply')
        ->post('/register', [
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'email' => 'ana.santos@tnhs.test',
            'lrn' => '123456789012',
            'year_level_applying' => 'Grade 11',
            'preferred_strand' => 'STEM',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect('/dashboard/student');

    $this->assertDatabaseHas('users', [
        'email' => 'ana.santos@tnhs.test',
        'preferred_strand' => 'STEM',
        'year_level_applying' => 'Grade 11',
    ]);
});

test('registration rejects a strand that is not active', function () {
    $this->from('/admission/apply')
        ->post('/register', [
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'email' => 'ana.santos@tnhs.test',
            'lrn' => '123456789012',
            'year_level_applying' => 'Grade 11',
            'preferred_strand' => 'UNKNOWN',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect('/admission/apply')
        ->assertSessionHasErrors('preferred_strand');
});

test('admin cannot delete a strand that students already selected', function () {
    $admin = makeStrandsAdmin();
    $strand = Strand::query()->where('code', 'STEM')->firstOrFail();

    User::create([
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan.delacruz@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '123456789013',
        'preferred_strand' => 'STEM',
        'admission_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->from('/admin/strands')
        ->delete("/admin/strands/{$strand->id}")
        ->assertRedirect('/admin/strands')
        ->assertSessionHasErrors('strand');

    $this->assertDatabaseHas('strands', [
        'id' => $strand->id,
        'code' => 'STEM',
    ]);
});

test('admin can delete an unused strand', function () {
    $admin = makeStrandsAdmin();
    $strand = Strand::query()->where('code', 'GAS')->firstOrFail();

    $this->actingAs($admin)
        ->from('/admin/strands')
        ->delete("/admin/strands/{$strand->id}")
        ->assertRedirect('/admin/strands');

    $this->assertDatabaseMissing('strands', [
        'id' => $strand->id,
    ]);
});
