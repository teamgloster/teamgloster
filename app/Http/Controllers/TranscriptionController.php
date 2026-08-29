<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranscriptionController extends Controller
{
    /**
     * Forward a short audio blob to Groq's Whisper endpoint and return
     * the transcribed text. Keeps the Groq API key server-side.
     */
    public function transcribe(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'max:15360'], // up to ~15MB
            'language' => ['nullable', 'string', 'max:8'],
            // Whisper accepts up to ~224 tokens for the prompt, roughly
            // 1000 chars. We allow a generous margin so a dynamic student
            // vocabulary hint fits comfortably.
            'prompt' => ['nullable', 'string', 'max:2000'],
            // Frontend hint about what kind of utterance to expect. When
            // "grade" we pick the fastest Whisper model since the audio is a
            // short two-digit number and low latency matters more than the
            // extra accuracy of the larger model.
            'mode' => ['nullable', 'string', 'in:grade,name'],
        ]);

        $apiKey = config('services.groq.key');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'Groq API key is not configured on the server.',
            ], 500);
        }

        $file = $request->file('audio');
        // Short utterances (a single grade) hallucinate on turbo. Always
        // use the configured model, defaulting to large-v3 for accuracy.
        $model = config('services.groq.transcription_model', 'whisper-large-v3');
        $baseUrl = rtrim((string) config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');

        try {
            $response = Http::timeout(20)
                ->withToken($apiKey)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName() ?: 'audio.webm',
                    ['Content-Type' => $file->getMimeType() ?: 'audio/webm']
                )
                ->asMultipart()
                ->post($baseUrl.'/audio/transcriptions', array_filter([
                    'model' => $model,
                    'language' => $request->input('language', 'en'),
                    'response_format' => 'json',
                    'temperature' => '0',
                    'prompt' => $request->input('prompt'),
                ]));
        } catch (\Throwable $e) {
            Log::error('Groq transcription request failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Failed to reach Groq transcription service.',
            ], 502);
        }

        if (! $response->successful()) {
            Log::warning('Groq transcription returned non-2xx', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'Groq transcription failed.',
                'status' => $response->status(),
            ], 502);
        }

        $text = trim((string) ($response->json('text') ?? ''));

        return response()->json([
            'text' => $text,
        ]);
    }
}
