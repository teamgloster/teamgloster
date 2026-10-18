<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\SchoolFormService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('saved teacher attendance appears on sf2 and sf9', function () {
    /** @var \Tests\TestCase $this */
    SchoolSetting::current()->update(['current_school_year' => '2026-2027']);

    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'forms-attendance@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $ana = User::create([
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'email' => 'ana.forms@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '111111111201',
        'admission_status' => 'approved',
        'gender' => 'female',
    ]);

    $ben = User::create([
        'first_name' => 'Ben',
        'last_name' => 'Reyes',
        'email' => 'ben.forms@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '111111111202',
        'admission_status' => 'approved',
        'gender' => 'male',
    ]);

    $grade = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-FORMS',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);

    $math = Subject::create([
        'name' => 'Mathematics',
        'code' => 'MATH7-FORMS',
        'year_level_id' => $grade->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    $english = Subject::create([
        'name' => 'English',
        'code' => 'ENG7-FORMS',
        'year_level_id' => $grade->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    $section = Section::create([
        'name' => 'Rosal',
        'code' => 'G7-FORMS-ROSAL',
        'year_level_id' => $grade->id,
        'school_year' => '2026-2027',
        'adviser_id' => $teacher->id,
        'capacity' => 40,
        'is_active' => true,
    ]);

    foreach ([$ana, $ben] as $student) {
        Enrollment::create([
            'user_id' => $student->id,
            'year_level_id' => $grade->id,
            'section_id' => $section->id,
            'school_year' => '2026-2027',
            'semester' => 'first',
            'status' => 'enrolled',
            'enrollment_type' => 'old',
            'enrolled_at' => now(),
        ]);
    }

    $mark = function (User $student, string $date, string $status, ?Subject $subject = null) use ($teacher, $section) {
        Attendance::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'subject_id' => $subject?->id,
            'teacher_id' => $teacher->id,
            'school_year' => '2026-2027',
            'class_key' => $subject
                ? 's'.$section->id.'-sub'.$subject->id
                : 's'.$section->id.'-advisory',
            'attendance_date' => $date,
            'status' => $status,
        ]);
    };

    $mark($ana, '2026-10-05', 'absent');
    $mark($ana, '2026-10-06', 'late', $math);
    $mark($ana, '2026-10-07', 'present');
    $mark($ana, '2026-10-07', 'absent', $math);
    $mark($ana, '2026-10-08', 'absent', $math);
    $mark($ana, '2026-10-08', 'absent', $english);
    $mark($ana, '2026-10-09', 'present', $math);
    $mark($ana, '2026-10-09', 'absent', $english);
    $mark($ben, '2026-10-10', 'absent');

    $forms = app(SchoolFormService::class);
    $sf2 = $forms->sf2($section, 10, '2026-2027');
    $anaRow = collect($sf2['learners'])->firstWhere('name', 'CRUZ, ANA');

    expect($anaRow['marks']['5'])->toBe('X')
        ->and($anaRow['marks']['6'])->toBe('T')
        ->and($anaRow['marks']['7'])->toBe('✓')
        ->and($anaRow['marks']['8'])->toBe('X')
        ->and($anaRow['marks']['9'])->toBe('✓')
        ->and($anaRow['marks'])->not->toHaveKey('10')
        ->and($anaRow['absent'])->toBe(2)
        ->and($anaRow['tardy'])->toBe(1);

    $october = collect($forms->sf9($ana, '2026-2027')['attendance_months'])->firstWhere('label', 'Oct');
    $total = collect($forms->sf9($ana, '2026-2027')['attendance_months'])->firstWhere('label', 'Total');

    expect($october['school_days'])->toBe(6)
        ->and($october['present'])->toBe(3)
        ->and($october['absent'])->toBe(2)
        ->and($total['school_days'])->toBe(6)
        ->and($total['present'])->toBe(3)
        ->and($total['absent'])->toBe(2);

    $this->actingAs($teacher)
        ->get(route('teacher.school-forms.sf2', ['section' => $section->id, 'month' => 10]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/SchoolForms/Sf2')
            ->where('learners.0.marks.5', 'X')
            ->where('learners.0.absent', 2)
            ->where('learners.0.tardy', 1));
});
