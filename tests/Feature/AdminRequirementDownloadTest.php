<?php

use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\StudentRequirement;
use App\Models\User;
use App\Models\YearLevel;
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
    /** @var \Tests\TestCase $this */
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
    /** @var \Tests\TestCase $this */
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
    /** @var \Tests\TestCase $this */
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

test('requirements page lists every student account even without uploads', function () {
    /** @var \Tests\TestCase $this */
    SchoolSetting::current()->update(['current_school_year' => '2026-2027']);

    $admin = makeRequirementsAdmin();
    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-REQ',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);

    $withFile = makeRequirementsStudent();
    StudentRequirement::create([
        'user_id' => $withFile->id,
        'requirement_type' => 'birth_certificate',
        'status' => 'pending',
    ]);

    $enrolledOnly = User::create([
        'first_name' => 'Ben',
        'last_name' => 'Cruz',
        'email' => 'ben.cruz@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '123456789016',
        'admission_status' => 'approved',
    ]);

    $notEnrolled = User::create([
        'first_name' => 'Cara',
        'last_name' => 'Abad',
        'email' => 'cara.abad@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '123456789017',
        'year_level_applying' => 'Grade 10',
        'admission_status' => 'incomplete',
    ]);

    foreach ([$withFile, $enrolledOnly] as $student) {
        Enrollment::create([
            'user_id' => $student->id,
            'year_level_id' => $grade7->id,
            'school_year' => '2026-2027',
            'semester' => 'first',
            'status' => 'enrolled',
            'enrollment_type' => 'new',
        ]);
    }

    $this->actingAs($admin)
        ->get('/admin/requirements')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin/Requirements')
            ->has('studentRequirements', 3)
            ->where('studentRequirements.0.user.last_name', 'Abad')
            ->where('studentRequirements.0.user.year_level_applying', 'Grade 10')
            ->where('studentRequirements.0.requirements', [])
            ->where('studentRequirements.1.user.last_name', 'Cruz')
            ->where('studentRequirements.1.user.year_level_applying', 'Grade 7')
            ->where('studentRequirements.1.requirements', [])
            ->where('studentRequirements.2.user.last_name', 'Santos')
            ->missing('studentRequirements.3')
        );

    expect($notEnrolled->enrollments)->toHaveCount(0);
});

test('guests cannot download requirement files', function () {
    /** @var \Tests\TestCase $this */
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
