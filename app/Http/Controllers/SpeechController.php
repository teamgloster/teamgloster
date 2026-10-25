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
        $pcm = $fast;
        $language = $validated['language'] ?? null;

        try {
            $response = $this->elevenLabs($text, $apiKey, $fast, $language);
            if ($fast && ! $response->successful() && $response->status() !== 401) {
                $pcm = false;
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

        if ($pcm) {
            return $this->streamBody($response, 'application/octet-stream', 'pcm_22050');
        }

        return response($response->body(), 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Fast talk-back uses the Flash model and a raw PCM stream so playback
     * can start before the full clip is generated.
     */
    private function elevenLabs(string $text, string $apiKey, bool $fast, ?string $language = null): \Illuminate\Http\Client\Response
    {
        $voiceId = (string) config('services.elevenlabs.voice_id', 'Xb7hH8MSUJpSbSDYk0k2');
        $baseUrl = rtrim((string) config('services.elevenlabs.base_url', 'https://api.elevenlabs.io/v1'), '/');
        $modelId = $fast
            ? (string) config('services.elevenlabs.flash_model', 'eleven_flash_v2_5')
            : (string) config('services.elevenlabs.model_id', 'eleven_multilingual_v2');

        $url = $baseUrl.'/text-to-speech/'.$voiceId;
        if ($fast) {
            $url .= '/stream?'.http_build_query([
                'output_format' => 'pcm_22050',
                'optimize_streaming_latency' => 4,
            ]);
        }

        $payload = [
            'text' => $text,
            'model_id' => $modelId,
        ];
        if (in_array($language, ['en', 'fil'], true)) {
            $payload['language_code'] = $language;
        }

        return Http::withOptions(['stream' => $fast])
            ->timeout($fast ? 12 : 30)
            ->connectTimeout(3)
            ->withHeaders([
                'xi-api-key' => $apiKey,
                'Accept' => $fast ? 'application/octet-stream' : 'audio/mpeg',
            ])
            ->post($url, $payload);
    }

    private function streamBody(\Illuminate\Http\Client\Response $response, string $contentType, string $format): Response
    {
        $body = $response->toPsrResponse()->getBody();

        return response()->stream(function () use ($body) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            while (! $body->eof()) {
                echo $body->read(2048);
                flush();
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-store',
            'X-Accel-Buffering' => 'no',
            'X-Speech-Format' => $format,
        ]);
    }
}
