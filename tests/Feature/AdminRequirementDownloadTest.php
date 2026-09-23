<?php

use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeRequirementsAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-requirements@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

function makeRequirementsStudent(): User
{
    return User::create([
        'first_name' => 'Ana',
        'last_name' => 'Santos',
        'email' => 'ana.requirements@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '123456789015',
        'year_level_applying' => 'Grade 7',
        'admission_status' => 'approved',
    ]);
}

test('admin can download a requirement uploaded by a student', function () {
    Storage::fake('public');

    $admin = makeRequirementsAdmin();
    $student = makeRequirementsStudent();
    $file = UploadedFile::fake()->create('Form-137.pdf', 120, 'application/pdf');

    $this->actingAs($student)
        ->post('/requirements/upload', [
            'requirement_type' => 'form_137',
            'file' => $file,
        ])
        ->assertRedirect();

    $requirement = StudentRequirement::query()->firstOrFail();

    $this->actingAs($admin)
        ->get("/admin/requirements/{$requirement->id}/download")
        ->assertOk()
        ->assertDownload('Form-137.pdf');
});

test('admin can view a requirement file in the browser', function () {
    Storage::fake('public');

    $admin = makeRequirementsAdmin();
    $student = makeRequirementsStudent();

    $this->actingAs($student)
        ->post('/requirements/upload', [
            'requirement_type' => 'birth_certificate',
            'file' => UploadedFile::fake()->create('psa-birth.pdf', 80, 'application/pdf'),
        ])
        ->assertRedirect();

    $requirement = StudentRequirement::query()->firstOrFail();

    $this->actingAs($admin)
        ->get("/admin/requirements/{$requirement->id}/view")
        ->assertOk();
});

test('admin requirements page includes download urls', function () {
    Storage::fake('public');

    $admin = makeRequirementsAdmin();
    $student = makeRequirementsStudent();

    $this->actingAs($student)
        ->post('/requirements/upload', [
            'requirement_type' => 'good_moral_certificate',
            'file' => UploadedFile::fake()->create('good-moral.pdf', 80, 'application/pdf'),
        ])
        ->assertRedirect();

    $requirement = StudentRequirement::query()->firstOrFail();

    $this->actingAs($admin)
        ->get('/admin/requirements')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin/Requirements')
            ->where('studentRequirements.0.requirements.0.download_url', route('admin.requirements.download', $requirement))
            ->where('studentRequirements.0.requirements.0.view_url', route('admin.requirements.view', $requirement))
        );
});

test('guests cannot download requirement files', function () {
    Storage::fake('public');

    $student = makeRequirementsStudent();

    $this->actingAs($student)
        ->post('/requirements/upload', [
            'requirement_type' => 'form_137',
            'file' => UploadedFile::fake()->create('Form-137.pdf', 80, 'application/pdf'),
        ]);

    $requirement = StudentRequirement::query()->firstOrFail();

    $this->post('/logout');

    $this->get("/admin/requirements/{$requirement->id}/download")
        ->assertRedirect('/');
});
