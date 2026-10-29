<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SpeechController extends Controller
{
    /**
     * Turn a short grade-entry prompt into speech with ElevenLabs.
     * The API key stays on the server.
     */
    public function speak(Request $request): Response|JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:400'],
            'fast' => ['sometimes', 'boolean'],
            'language' => ['sometimes', 'nullable', 'string', 'in:en,fil'],
        ]);

        $apiKey = config('services.elevenlabs.key');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'ElevenLabs API key is not configured on the server.',
            ], 500);
        }

        $text = trim(preg_replace('/\s+/', ' ', $validated['text']) ?? '');
        if ($text === '') {
            return response()->json([
                'error' => 'Nothing to speak.',
            ], 422);
        }

        $fast = (bool) ($validated['fast'] ?? false);
        $language = $validated['language'] ?? null;

        try {
            $response = $this->elevenLabs($text, $apiKey, $fast, $language);
            if ($fast && ! $response->successful() && $response->status() !== 401) {
                $response = $this->elevenLabs($text, $apiKey, false, $language);
            }
        } catch (\Throwable $e) {
            Log::error('ElevenLabs speech request failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Failed to reach ElevenLabs.',
            ], 502);
        }

        if (! $response->successful()) {
            Log::warning('ElevenLabs speech returned non-2xx', [
                'status' => $response->status(),
            ]);

            $error = $response->status() === 401
                ? 'ElevenLabs rejected the API key. Use the secret key that starts with sk_.'
                : 'ElevenLabs speech failed.';

            return response()->json([
                'error' => $error,
                'status' => $response->status(),
            ], 502);
        }

        return response($response->body(), 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * One complete MP3 per phrase. Chopped raw audio was arriving in tiny
     * pieces and played back as crackle and stutters.
     */
    private function elevenLabs(string $text, string $apiKey, bool $fast, ?string $language = null): \Illuminate\Http\Client\Response
    {
        $voiceId = (string) config('services.elevenlabs.voice_id', '21m00Tcm4TlvDq8ikWAM');
        $baseUrl = rtrim((string) config('services.elevenlabs.base_url', 'https://api.elevenlabs.io/v1'), '/');
        $modelId = $fast
            ? (string) config('services.elevenlabs.flash_model', 'eleven_flash_v2_5')
            : (string) config('services.elevenlabs.model_id', 'eleven_multilingual_v2');

        $url = $baseUrl.'/text-to-speech/'.$voiceId.'?'.http_build_query([
            'output_format' => 'mp3_44100_128',
        ]);

        $payload = [
            'text' => $text,
            'model_id' => $modelId,
            'voice_settings' => [
                'stability' => 0.62,
                'similarity_boost' => 0.8,
                'style' => 0,
                'use_speaker_boost' => true,
            ],
        ];
        if (in_array($language, ['en', 'fil'], true)) {
            $payload['language_code'] = $language;
        }

        return Http::timeout($fast ? 20 : 30)
            ->connectTimeout(5)
            ->withHeaders([
                'xi-api-key' => $apiKey,
                'Accept' => 'audio/mpeg',
            ])
            ->post($url, $payload);
    }
}
