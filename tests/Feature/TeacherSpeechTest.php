<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('a teacher can turn a prompt into speech through elevenlabs', function () {
    /** @var \Tests\TestCase $this */
    config([
        'services.elevenlabs.key' => 'test-elevenlabs-key',
        'services.elevenlabs.voice_id' => '21m00Tcm4TlvDq8ikWAM',
        'services.elevenlabs.model_id' => 'eleven_multilingual_v2',
        'services.elevenlabs.base_url' => 'https://api.elevenlabs.io/v1',
    ]);

    Http::fake([
        'https://api.elevenlabs.io/*' => Http::response('FAKE-MP3', 200, [
            'Content-Type' => 'audio/mpeg',
        ]),
    ]);

    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'speech-teacher@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher)
        ->postJson('/teacher/speak', ['text' => 'Term one.'])
        ->assertOk()
        ->assertHeader('content-type', 'audio/mpeg');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/text-to-speech/21m00Tcm4TlvDq8ikWAM')
            && $request['text'] === 'Term one.'
            && $request['model_id'] === 'eleven_multilingual_v2'
            && $request->hasHeader('xi-api-key', 'test-elevenlabs-key');
    });
});

test('talk back asks elevenlabs for a low latency stream', function () {
    /** @var \Tests\TestCase $this */
    config([
        'services.elevenlabs.key' => 'test-elevenlabs-key',
        'services.elevenlabs.voice_id' => '21m00Tcm4TlvDq8ikWAM',
        'services.elevenlabs.flash_model' => 'eleven_flash_v2_5',
        'services.elevenlabs.base_url' => 'https://api.elevenlabs.io/v1',
    ]);

    Http::fake([
        'https://api.elevenlabs.io/*' => Http::response('PCM', 200, [
            'Content-Type' => 'application/octet-stream',
        ]),
    ]);

    $teacher = User::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'speech-fast@tnhs.test',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher)
        ->postJson('/teacher/speak', ['text' => 'Term one.', 'fast' => true])
        ->assertOk()
        ->assertHeader('x-speech-format', 'pcm_22050');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/text-to-speech/21m00Tcm4TlvDq8ikWAM/stream')
            && str_contains($request->url(), 'output_format=pcm_22050')
            && str_contains($request->url(), 'optimize_streaming_latency=4')
            && $request['text'] === 'Term one.'
            && $request['model_id'] === 'eleven_flash_v2_5';
    });
});

test('a student cannot use the teacher speech endpoint', function () {
    /** @var \Tests\TestCase $this */
    $student = User::create([
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'email' => 'speech-student@tnhs.test',
        'password' => 'password',
        'role' => 'student',
        'lrn' => '111111111301',
        'admission_status' => 'approved',
    ]);

    $this->actingAs($student)
        ->postJson('/teacher/speak', ['text' => 'Term one.'])
        ->assertForbidden();
});

test('guests are sent away from teacher speech', function () {
    /** @var \Tests\TestCase $this */
    $this->post('/teacher/speak', ['text' => 'Term one.'])
        ->assertRedirect();
});

test('each school role can use the shared speech endpoint', function (string $role) {
    /** @var \Tests\TestCase $this */
    config([
        'services.elevenlabs.key' => 'test-elevenlabs-key',
        'services.elevenlabs.voice_id' => '21m00Tcm4TlvDq8ikWAM',
        'services.elevenlabs.model_id' => 'eleven_multilingual_v2',
        'services.elevenlabs.base_url' => 'https://api.elevenlabs.io/v1',
    ]);

    Http::fake([
        'https://api.elevenlabs.io/*' => Http::response('FAKE-MP3', 200, [
            'Content-Type' => 'audio/mpeg',
        ]),
    ]);

    $user = User::create([
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'email' => "speech-shared-{$role}@tnhs.test",
        'password' => 'password',
        'role' => $role,
        ...($role === 'student' ? [
            'lrn' => '111111111302',
            'admission_status' => 'approved',
        ] : []),
    ]);

    $this->actingAs($user)
        ->postJson('/speak', ['text' => 'Term one.'])
        ->assertOk()
        ->assertHeader('content-type', 'audio/mpeg');
})->with(['teacher', 'administrator', 'student', 'registrar']);

test('guests are sent away from the shared speech endpoint', function () {
    /** @var \Tests\TestCase $this */
    $this->post('/speak', ['text' => 'Term one.'])
        ->assertRedirect();
});
