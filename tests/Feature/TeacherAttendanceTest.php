<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\SectionSubjectTeacher;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function attendanceTeacher(string $email = 'attendance-teacher@tnhs.test'): User
{
    return User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => $email,
        'password' => 'password',
        'role' => 'teacher',
    ]);
}

function attendanceStudent(string $first, string $last, string $email, string $lrn): User
{
    return User::create([
        'first_name' => $first,
        'last_name' => $last,
        'email' => $email,
        'password' => 'password',
        'role' => 'student',
        'lrn' => $lrn,
        'admission_status' => 'approved',
    ]);
}

function enrollAttendanceStudent(User $student, YearLevel $yearLevel, Section $section): void
{
    Enrollment::create([
        'user_id' => $student->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $section->id,
        'school_year' => '2025-2026',
        'semester' => 'first',
        'status' => 'enrolled',
        'enrollment_type' => 'old',
        'enrolled_at' => now(),
    ]);
}

function attendanceCatalog(): array
{
    SchoolSetting::current()->update(['current_school_year' => '2025-2026']);

    $grade7 = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7-ATT',
        'level_type' => 'junior_high',
        'is_active' => true,
    ]);

    $math = Subject::create([
        'name' => 'Mathematics',
        'code' => 'MATH7-ATT',
        'year_level_id' => $grade7->id,
        'subject_type' => 'core',
        'semester' => 'full_year',
        'is_active' => true,
    ]);

    $rosal = Section::create([
        'name' => 'Rosal',
        'code' => 'G7-ROSAL',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $rizal = Section::create([
        'name' => 'Rizal',
        'code' => 'G7-RIZAL',
        'year_level_id' => $grade7->id,
        'school_year' => '2025-2026',
        'capacity' => 40,
        'is_active' => true,
    ]);

    $ana = attendanceStudent('Ana', 'Cruz', 'ana.att@tnhs.test', '111111111101');
    $ben = attendanceStudent('Ben', 'Dela Cruz', 'ben.att@tnhs.test', '111111111102');
    $cara = attendanceStudent('Cara', 'Reyes', 'cara.att@tnhs.test', '111111111103');

    enrollAttendanceStudent($ana, $grade7, $rosal);
    enrollAttendanceStudent($ben, $grade7, $rosal);
    enrollAttendanceStudent($cara, $grade7, $rizal);

    return compact('grade7', 'math', 'rosal', 'rizal', 'ana', 'ben', 'cara');
}

test('attendance lists every enrolled student in a class the teacher handles', function () {
    /** @var \Tests\TestCase $this */
    $teacher = attendanceTeacher();
    ['math' => $math, 'rosal' => $rosal] = attendanceCatalog();

    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
    ]);
    SectionSubjectTeacher::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
        'section_id' => $rosal->id,
        'school_year' => '2025-2026',
        'is_active' => true,
    ]);

    $this->actingAs($teacher)
        ->get('/teacher/attendance')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Teacher/Attendance')
            ->has('classes', 1)
            ->where('classes.0.label', 'Grade 7 Rosal · Mathematics')
            ->has('students', 2)
            ->where('students.0.last_name', 'Cruz')
            ->where('students.0.status', null)
            ->where('students.1.last_name', 'Dela Cruz')
        );
});

test('a teacher can save attendance for every student in the class', function () {
    /** @var \Tests\TestCase $this */
    $teacher = attendanceTeacher();
    ['math' => $math, 'rosal' => $rosal, 'ana' => $ana, 'ben' => $ben] = attendanceCatalog();

    SectionSubjectTeacher::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
        'section_id' => $rosal->id,
        'school_year' => '2025-2026',
        'is_active' => true,
    ]);

    $classKey = 's'.$rosal->id.'-sub'.$math->id;
    $date = now()->toDateString();

    $this->actingAs($teacher)
        ->put('/teacher/attendance', [
            'class_key' => $classKey,
            'date' => $date,
            'records' => [
                ['student_id' => $ana->id, 'status' => 'present', 'remarks' => null],
                ['student_id' => $ben->id, 'status' => 'absent', 'remarks' => 'Fever'],
            ],
        ])
        ->assertRedirect(route('teacher.attendance', [
            'class' => $classKey,
            'date' => $date,
        ]));

    $this->actingAs($teacher)
        ->get('/teacher/attendance?class='.$classKey.'&date='.$date)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('students.0.status', 'present')
            ->where('students.1.status', 'absent')
            ->where('students.1.remarks', 'Fever')
        );

    expect(Attendance::query()->count())->toBe(2);
});

test('attendance cannot be saved for a student outside the class', function () {
    /** @var \Tests\TestCase $this */
    $teacher = attendanceTeacher();
    ['math' => $math, 'rosal' => $rosal, 'ana' => $ana, 'ben' => $ben, 'cara' => $cara] = attendanceCatalog();

    SectionSubjectTeacher::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
        'section_id' => $rosal->id,
        'school_year' => '2025-2026',
        'is_active' => true,
    ]);

    $this->actingAs($teacher)
        ->from('/teacher/attendance')
        ->put('/teacher/attendance', [
            'class_key' => 's'.$rosal->id.'-sub'.$math->id,
            'date' => now()->toDateString(),
            'records' => [
                ['student_id' => $ana->id, 'status' => 'present'],
                ['student_id' => $ben->id, 'status' => 'present'],
                ['student_id' => $cara->id, 'status' => 'absent'],
            ],
        ])
        ->assertRedirect('/teacher/attendance')
        ->assertSessionHasErrors('records');

    expect(Attendance::query()->count())->toBe(0);
});

test('another teacher cannot save attendance for a class they do not handle', function () {
    /** @var \Tests\TestCase $this */
    $teacher = attendanceTeacher();
    $other = attendanceTeacher('other-attendance@tnhs.test');
    ['math' => $math, 'rosal' => $rosal, 'ana' => $ana, 'ben' => $ben] = attendanceCatalog();

    SectionSubjectTeacher::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $math->id,
        'section_id' => $rosal->id,
        'school_year' => '2025-2026',
        'is_active' => true,
    ]);

    $this->actingAs($other)
        ->put('/teacher/attendance', [
            'class_key' => 's'.$rosal->id.'-sub'.$math->id,
            'date' => now()->toDateString(),
            'records' => [
                ['student_id' => $ana->id, 'status' => 'present'],
                ['student_id' => $ben->id, 'status' => 'present'],
            ],
        ])
        ->assertNotFound();
});

test('an adviser can take attendance for every student in the advisory section', function () {
    /** @var \Tests\TestCase $this */
    $teacher = attendanceTeacher();
    ['rosal' => $rosal] = attendanceCatalog();
    $rosal->update(['adviser_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->get('/teacher/attendance')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('classes', 1)
            ->where('classes.0.kind', 'advisory')
            ->where('classes.0.label', 'Grade 7 Rosal · Advisory')
            ->has('students', 2)
            ->where('students.0.last_name', 'Cruz')
            ->where('students.1.last_name', 'Dela Cruz')
        );
});

test('students cannot open the teacher attendance page', function () {
    /** @var \Tests\TestCase $this */
    attendanceCatalog();
    $student = attendanceStudent('Dina', 'Lopez', 'dina.att@tnhs.test', '111111111104');

    $this->actingAs($student)
        ->get('/teacher/attendance')
        ->assertForbidden();
});

test('guests are sent away from teacher attendance', function () {
    /** @var \Tests\TestCase $this */
    $this->get('/teacher/attendance')->assertRedirect('/');
});

test('a student can check whether they were present on a day', function () {
    /** @var \Tests\TestCase $this */
    $teacher = attendanceTeacher();
    ['math' => $math, 'rosal' => $rosal, 'ana' => $ana, 'ben' => $ben] = attendanceCatalog();
    $date = now('Asia/Manila')->toDateString();

    Attendance::create([
        'student_id' => $ana->id,
        'section_id' => $rosal->id,
        'subject_id' => $math->id,
        'teacher_id' => $teacher->id,
        'school_year' => '2025-2026',
        'class_key' => 's'.$rosal->id.'-sub'.$math->id,
        'attendance_date' => $date,
        'status' => 'present',
    ]);
    Attendance::create([
        'student_id' => $ben->id,
        'section_id' => $rosal->id,
        'subject_id' => $math->id,
        'teacher_id' => $teacher->id,
        'school_year' => '2025-2026',
        'class_key' => 's'.$rosal->id.'-sub'.$math->id,
        'attendance_date' => $date,
        'status' => 'absent',
        'remarks' => 'Fever',
    ]);

    $this->actingAs($ana)
        ->get('/student/attendance?date='.$date)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Student/Attendance')
            ->has('records', 1)
            ->where('records.0.status', 'present')
            ->where('records.0.subject_name', 'Mathematics')
            ->where('records.0.section_name', 'Rosal')
            ->where('summary.present', 1)
            ->where('summary.absent', 0)
            ->has('history', 1)
        );
});

test('a student sees an empty day when attendance was not recorded', function () {
    /** @var \Tests\TestCase $this */
    ['ana' => $ana] = attendanceCatalog();

    $this->actingAs($ana)
        ->get('/student/attendance')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Student/Attendance')
            ->has('records', 0)
            ->where('summary.present', 0)
        );
});

test('a teacher cannot open a student attendance page', function () {
    /** @var \Tests\TestCase $this */
    $teacher = attendanceTeacher();

    $this->actingAs($teacher)
        ->get('/student/attendance')
        ->assertForbidden();
});
