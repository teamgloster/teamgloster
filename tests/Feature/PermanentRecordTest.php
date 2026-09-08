<?php

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\StudentPermanentRecord;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function recordAdmin(): User
{
    return User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'sp10-admin@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
}

function recordRegistrar(): User
{
    return User::create([
        'first_name' => 'School',
        'last_name' => 'Registrar',
        'email' => 'sp10-registrar@tnhs.test',
        'password' => 'password',
        'role' => 'registrar',
    ]);
}

function recordStudent(string $lrn = '111111111111'): User
{
    return User::create([
        'first_name' => 'Ana',
        'last_name' => 'Reyes',
        'email' => $lrn.'@student.tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => $lrn,
        'admission_status' => 'approved',
    ]);
}

beforeEach(function () {
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);
});

test('admin SP-10 finder redirects to school forms', function () {
    $this->actingAs(recordAdmin())
        ->get('/admin/permanent-records')
        ->assertRedirect(route('admin.school-forms'));
});

test('registrar can open the SP-10 finder', function () {
    $this->actingAs(recordRegistrar())
        ->get('/registrar/permanent-records')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/PermanentRecords/Index')
            ->where('viewer', 'registrar')
        );
});

test('admin can upload and download an old SP-10 file', function () {
    Storage::fake('public');
    $admin = recordAdmin();
    $student = recordStudent('222222222222');
    $file = UploadedFile::fake()->create('old-form-137.pdf', 120, 'application/pdf');

    $this->actingAs($admin)
        ->post("/admin/students/{$student->id}/permanent-records", [
            'file' => $file,
            'school_year' => '2023-2024',
            'notes' => 'Transferee record',
        ])
        ->assertRedirect();

    $record = StudentPermanentRecord::query()->first();
    expect($record)->not->toBeNull()
        ->and($record->student_id)->toBe($student->id)
        ->and($record->school_year)->toBe('2023-2024')
        ->and($record->original_filename)->toBe('old-form-137.pdf');

    Storage::disk('public')->assertExists($record->file_path);

    $this->actingAs($admin)
        ->get("/admin/permanent-records/{$record->id}/download")
        ->assertSuccessful();
});

test('teacher cannot open admin school forms through the old SP-10 path', function () {
    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'sp10-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher)
        ->get('/admin/permanent-records')
        ->assertForbidden();
});

test('finder marks learners who already have encoded grades', function () {
    $student = recordStudent('333333333333');
    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-SP10B',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);
    Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
    ]);
    $subject = Subject::create([
        'name' => 'Science 7',
        'code' => 'SCI7SP10',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);
    Grade::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'school_year' => '2025-2026',
        'final_grade' => 88,
    ]);

    $this->actingAs(recordRegistrar())
        ->get('/registrar/permanent-records')
        ->assertInertia(fn ($page) => $page
            ->where('students.0.has_grades', true)
        );
});
