<?php

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\SectionSubjectTeacher;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('a teacher can ask the voice assistant', function () {
    /** @var \Tests\TestCase $this */
    config([
        'services.groq.key' => 'test-groq-key',
        'services.groq.chat_model' => 'openai/gpt-oss-20b',
        'services.groq.base_url' => 'https://api.groq.com/openai/v1',
    ]);

    fakeTeacherAssistant('Open Attendance, mark each student, then press Save attendance.');

    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'assistant-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher)
        ->postJson('/teacher/assistant', [
            'message' => 'How do I save attendance?',
            'page' => 'Attendance',
            'history' => [],
        ])
        ->assertOk()
        ->assertJsonPath('reply', 'Open Attendance, mark each student, then press Save attendance.');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
            && $request['model'] === 'openai/gpt-oss-20b'
            && $request['messages'][1]['content'] === 'How do I save attendance?';
    });
});

test('the assistant sees only the signed-in teacher records', function () {
    /** @var \Tests\TestCase $this */
    config([
        'services.groq.key' => 'test-groq-key',
        'services.groq.chat_model' => 'openai/gpt-oss-20b',
        'services.groq.base_url' => 'https://api.groq.com/openai/v1',
    ]);

    fakeTeacherAssistant('You have 2 students in your advisory class.');

    $schoolYear = SchoolSetting::currentSchoolYear();
    $yearLevel = YearLevel::create([
        'name' => 'Grade 7',
        'code' => 'G7',
        'level_type' => 'junior_high',
    ]);
    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'records-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);
    $otherTeacher = User::create([
        'first_name' => 'Pedro',
        'last_name' => 'Reyes',
        'email' => 'other-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);
    $section = Section::create([
        'year_level_id' => $yearLevel->id,
        'name' => 'Rizal',
        'adviser_id' => $teacher->id,
        'school_year' => $schoolYear,
    ]);
    $otherSection = Section::create([
        'year_level_id' => $yearLevel->id,
        'name' => 'Bonifacio',
        'adviser_id' => $otherTeacher->id,
        'school_year' => $schoolYear,
    ]);
    $subject = Subject::create([
        'name' => 'Mathematics',
        'code' => 'MATH7-ASSIST',
        'year_level_id' => $yearLevel->id,
    ]);
    $teacher->teachableSubjects()->attach($subject->id);
    SectionSubjectTeacher::create([
        'section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'school_year' => $schoolYear,
        'semester' => 'full_year',
    ]);

    $ana = User::create([
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'email' => 'ana-records@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '100000001001',
        'admission_status' => 'approved',
    ]);
    $ben = User::create([
        'first_name' => 'Ben',
        'last_name' => 'Dela Cruz',
        'email' => 'ben-records@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '100000001002',
        'admission_status' => 'approved',
    ]);
    $pending = User::create([
        'first_name' => 'Pending',
        'last_name' => 'Kid',
        'email' => 'pending-records@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '100000001003',
        'admission_status' => 'pending',
    ]);
    $secret = User::create([
        'first_name' => 'Secret',
        'last_name' => 'Student',
        'email' => 'secret-records@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '100000001004',
        'admission_status' => 'approved',
    ]);

    foreach ([[$ana, $section], [$ben, $section]] as [$student, $class]) {
        Enrollment::create([
            'user_id' => $student->id,
            'year_level_id' => $yearLevel->id,
            'section_id' => $class->id,
            'school_year' => $schoolYear,
            'status' => 'enrolled',
        ]);
    }
    Enrollment::create([
        'user_id' => $pending->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $section->id,
        'school_year' => $schoolYear,
        'status' => 'pending',
    ]);
    Enrollment::create([
        'user_id' => $secret->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $otherSection->id,
        'school_year' => $schoolYear,
        'status' => 'enrolled',
    ]);

    Grade::create([
        'student_id' => $ana->id,
        'subject_id' => $subject->id,
        'section_id' => $section->id,
        'teacher_id' => $teacher->id,
        'school_year' => $schoolYear,
        'term_1' => 88,
    ]);
    Grade::create([
        'student_id' => $secret->id,
        'subject_id' => $subject->id,
        'section_id' => $otherSection->id,
        'teacher_id' => $otherTeacher->id,
        'school_year' => $schoolYear,
        'term_1' => 41,
    ]);
    Attendance::create([
        'student_id' => $ben->id,
        'section_id' => $section->id,
        'teacher_id' => $teacher->id,
        'school_year' => $schoolYear,
        'class_key' => 's'.$section->id.'-advisory',
        'attendance_date' => now()->toDateString(),
        'status' => 'absent',
    ]);

    $this->actingAs($teacher)
        ->postJson('/teacher/assistant', [
            'message' => 'How many students I have?',
            'page' => 'Dashboard',
        ])
        ->assertOk();

    Http::assertSent(function ($request) {
        $records = $request['messages'][0]['content'];

        return str_contains($records, 'ADVISORY STUDENT COUNT: 2')
            && str_contains($records, 'Ana Cruz, LRN 100000001001')
            && str_contains($records, 'Ben Dela Cruz, LRN 100000001002')
            && str_contains($records, 'term 1 88')
            && str_contains($records, 'absent: Ben Dela Cruz')
            && ! str_contains($records, 'Secret Student')
            && ! str_contains($records, 'Pending Kid')
            && ! str_contains($records, 'term 1 41');
    });
});

test('the assistant tells the signed-in teacher who they are', function () {
    /** @var \Tests\TestCase $this */
    Http::fake();

    $teacher = User::create([
        'first_name' => 'Juan',
        'middle_name' => 'Dela',
        'last_name' => 'Cruz',
        'email' => 'whoami-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher)
        ->postJson('/teacher/assistant', ['message' => 'Who am I?'])
        ->assertOk()
        ->assertJsonPath('reply', 'You are Juan Dela Cruz, a teacher at Tambo National High School.');

    $this->actingAs($teacher)
        ->postJson('/teacher/assistant', [
            'message' => 'Who am I?',
            'language' => 'tl',
        ])
        ->assertOk()
        ->assertJsonPath('reply', 'Ikaw si Juan Dela Cruz. Guro ka sa Tambo National High School.');

    Http::assertNothingSent();
});

test('the assistant does not answer an off-topic question', function () {
    /** @var \Tests\TestCase $this */
    config([
        'services.groq.key' => 'test-groq-key',
        'services.groq.chat_model' => 'openai/gpt-oss-20b',
        'services.groq.base_url' => 'https://api.groq.com/openai/v1',
    ]);

    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Adobo is made by marinating sliced pork in vinegar.']],
            ],
        ]),
    ]);

    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'offtopic-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher)
        ->postJson('/teacher/assistant', ['message' => 'How to make adobo?'])
        ->assertOk()
        ->assertJsonPath('reply', 'I can only help with your teacher portal and your classes.');

    Http::assertSentCount(1);
});

test('a student cannot use the teacher assistant', function () {
    /** @var \Tests\TestCase $this */
    $student = User::create([
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'email' => 'assistant-student@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '111111111303',
        'admission_status' => 'approved',
    ]);

    $this->actingAs($student)
        ->postJson('/teacher/assistant', ['message' => 'Hello'])
        ->assertForbidden();
});

test('admin student and registrar can ask who they are', function () {
    /** @var \Tests\TestCase $this */
    Http::fake();

    $student = User::create([
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'email' => 'who-student@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '111111111404',
        'admission_status' => 'approved',
    ]);
    $admin = User::create([
        'first_name' => 'Liza',
        'last_name' => 'Ramos',
        'email' => 'who-admin@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);
    $registrar = User::create([
        'first_name' => 'Paolo',
        'last_name' => 'Mendoza',
        'email' => 'who-registrar@tnhs.test',
        'password' => 'password',
        'role' => 'registrar',
    ]);

    $this->actingAs($student)
        ->postJson('/assistant', ['message' => 'Who am I?'])
        ->assertOk()
        ->assertJsonPath('reply', 'You are Ana Cruz, a student at Tambo National High School.');

    $this->actingAs($admin)
        ->postJson('/assistant', ['message' => 'Who am I?'])
        ->assertOk()
        ->assertJsonPath('reply', 'You are Liza Ramos, an administrator at Tambo National High School.');

    $this->actingAs($registrar)
        ->postJson('/assistant', ['message' => 'Who am I?', 'language' => 'tl'])
        ->assertOk()
        ->assertJsonPath('reply', 'Ikaw si Paolo Mendoza. Rehistrador ka sa Tambo National High School.');

    Http::assertNothingSent();
});

test('an administrator assistant sees school counts', function () {
    /** @var \Tests\TestCase $this */
    config([
        'services.groq.key' => 'test-groq-key',
        'services.groq.chat_model' => 'openai/gpt-oss-20b',
        'services.groq.base_url' => 'https://api.groq.com/openai/v1',
    ]);
    fakeTeacherAssistant('The school has students on the Students page.');

    $admin = User::create([
        'first_name' => 'Liza',
        'last_name' => 'Ramos',
        'email' => 'counts-admin@tnhs.test',
        'password' => 'password',
        'role' => 'administrator',
    ]);

    $this->actingAs($admin)
        ->postJson('/assistant', [
            'message' => 'How many students are enrolled?',
            'page' => 'Dashboard',
        ])
        ->assertOk();

    Http::assertSent(function ($request) {
        $records = (string) ($request['messages'][0]['content'] ?? '');

        return str_contains($records, 'STUDENT COUNT:')
            && str_contains($records, 'Students, Admissions, Enrollments')
            && ! str_contains($records, 'ADVISORY STUDENT COUNT');
    });
});

function fakeTeacherAssistant(string $answer): void
{
    Http::fake(function ($request) use ($answer) {
        $system = (string) ($request['messages'][0]['content'] ?? '');
        $content = str_contains($system, 'ON_TOPIC or OFF_TOPIC') ? 'ON_TOPIC' : $answer;

        return Http::response([
            'choices' => [
                ['message' => ['content' => $content]],
            ],
        ]);
    });
}
