<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use App\Services\PortalAssistantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    public function __construct(private PortalAssistantContext $records) {}

    /**
     * Answer a teacher in the portal helper. The Groq key stays on the server.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'page' => ['nullable', 'string', 'max:80'],
            'language' => ['nullable', 'string', 'in:en,tl'],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.role' => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:1000'],
        ]);

        $message = trim(preg_replace('/\s+/', ' ', $validated['message']) ?? '');
        if ($message === '') {
            return response()->json([
                'error' => 'Type or say a question first.',
            ], 422);
        }

        $teacher = $request->user();
        abort_unless($teacher instanceof User, 401);
        $language = ($validated['language'] ?? 'en') === 'tl' ? 'tl' : 'en';

        if ($this->asksWhoTheyAre($message)) {
            return response()->json([
                'reply' => $this->identityReply($teacher, $language),
            ]);
        }

        $apiKey = config('services.groq.key');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'The assistant is not configured on the server yet.',
            ], 500);
        }

        $page = trim((string) ($validated['page'] ?? ''));
        $history = collect($validated['history'] ?? [])
            ->map(fn (array $turn) => [
                'role' => $turn['role'],
                'content' => trim(preg_replace('/\s+/', ' ', $turn['content']) ?? ''),
            ])
            ->filter(fn (array $turn) => $turn['content'] !== '')
            ->values()
            ->all();

        $baseUrl = rtrim((string) config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        $model = (string) config('services.groq.chat_model', 'openai/gpt-oss-20b');

        $verdict = $this->complete($apiKey, $baseUrl, $model, [
            ['role' => 'system', 'content' => $this->topicInstructions($teacher->role)],
            ['role' => 'user', 'content' => $this->topicQuestion($message, $history)],
        ], 200, 0);
        if ($verdict['error'] !== null) {
            return $verdict['error'];
        }
        $topic = $verdict['content'] !== '' ? $verdict['content'] : $verdict['reasoning'];
        if ($topic === '') {
            return response()->json([
                'error' => 'The assistant had no answer. Try asking in a different way.',
            ], 502);
        }
        if (! $this->onTopic($topic)) {
            return response()->json([
                'reply' => $this->refusal($language, $teacher->role),
            ]);
        }

        $answer = $this->complete($apiKey, $baseUrl, $model, [
            ['role' => 'system', 'content' => $this->instructions($page, $teacher, $language)],
            ...$history,
            ['role' => 'user', 'content' => $message],
        ], 400, 0.4);
        if ($answer['error'] !== null) {
            return $answer['error'];
        }

        $reply = $this->keepOnTopic($answer['content'], $language, $teacher->role);
        if ($reply === '') {
            return response()->json([
                'error' => 'The assistant had no answer. Try asking in a different way.',
            ], 502);
        }

        return response()->json([
            'reply' => $reply,
        ]);
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{content: string, reasoning: string, error: ?JsonResponse}
     */
    private function complete(
        string $apiKey,
        string $baseUrl,
        string $model,
        array $messages,
        int $maxTokens,
        float $temperature,
    ): array {
        $payload = [
            'model' => $model,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
        ];
        if (str_contains($model, 'gpt-oss')) {
            $payload['reasoning_effort'] = 'low';
        }

        try {
            $response = Http::timeout(25)
                ->connectTimeout(5)
                ->withToken($apiKey)
                ->acceptJson()
                ->post($baseUrl.'/chat/completions', $payload);
        } catch (\Throwable $e) {
            Log::error('Teacher assistant request failed', ['error' => $e->getMessage()]);

            return [
                'content' => '',
                'reasoning' => '',
                'error' => response()->json([
                    'error' => 'The assistant could not be reached. Try again in a moment.',
                ], 502),
            ];
        }

        if (! $response->successful()) {
            Log::warning('Teacher assistant returned non-2xx', [
                'status' => $response->status(),
            ]);

            return [
                'content' => '',
                'reasoning' => '',
                'error' => response()->json([
                    'error' => 'The assistant could not answer just now. Try again.',
                ], 502),
            ];
        }

        return [
            'content' => trim((string) data_get($response->json(), 'choices.0.message.content', '')),
            'reasoning' => trim((string) data_get($response->json(), 'choices.0.message.reasoning', '')),
            'error' => null,
        ];
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     */
    private function topicQuestion(string $message, array $history): string
    {
        $previous = collect($history)->reverse()->first(
            fn (array $turn) => $turn['role'] === 'user' && $turn['content'] !== $message
        );
        if (! $previous) {
            return $message;
        }

        return "Previous question: {$previous['content']}\nCurrent question: {$message}";
    }

    private function topicInstructions(string $role): string
    {
        $scope = match ($role) {
            'student' => 'their own enrollment, grades, attendance, requirements, profile, or a page in the student portal',
            'administrator' => 'students, teachers, admissions, enrollments, sections, subjects, accounts, settings, or a page in the admin portal',
            'registrar' => 'year levels, sections, students, grading records, enrollment, or a page in the registrar portal',
            default => 'this teacher\'s students, classes, subjects, grades, attendance, school forms, or a page in the teacher portal',
        };

        return <<<TEXT
You sort one question for a high school portal.
Reply with exactly one word: ON_TOPIC or OFF_TOPIC.
Do not answer the question.
ON_TOPIC only when the current question is about the signed-in person's portal, opening one of its pages, how to use those pages, their portal password, who they are, or {$scope}.
OFF_TOPIC for everything else. Recipes, cooking, news, sports, entertainment, general knowledge, homework answers, and personal advice are OFF_TOPIC.
If the current question asks for anything outside the portal, reply OFF_TOPIC.
TEXT;
    }

    private function onTopic(string $verdict): bool
    {
        $token = strtoupper((string) preg_replace('/[^A-Za-z_]/', '', $verdict));

        return $token === 'ON_TOPIC';
    }

    private function asksWhoTheyAre(string $message): bool
    {
        $spoken = strtolower($message);
        $spoken = preg_replace('/[^a-z0-9\s]/', ' ', $spoken) ?? '';
        $spoken = trim(preg_replace('/\s+/', ' ', $spoken) ?? '');

        return (bool) preg_match(
            '/^(who am i|who i am|what is my name|whats my name|sino ako|sino ba ako|ano ang pangalan ko|anong pangalan ko|ano pangalan ko)$/',
            $spoken,
        );
    }

    private function identityReply(User $teacher, string $language): string
    {
        $name = trim(implode(' ', array_filter([
            $teacher->first_name,
            $teacher->middle_name,
            $teacher->last_name,
            $teacher->suffix,
        ])));
        $advisory = '';
        if ($teacher->role === 'teacher') {
            $section = Section::query()
                ->where('adviser_id', $teacher->id)
                ->with('yearLevel')
                ->orderByDesc('id')
                ->first();
            $advisory = $section
                ? trim(($section->yearLevel?->name ? $section->yearLevel->name.' ' : '').$section->name)
                : '';
        }

        $roleLine = match ($teacher->role) {
            'student' => $language === 'tl' ? 'Estudyante ka' : 'a student',
            'registrar' => $language === 'tl' ? 'Rehistrador ka' : 'a registrar',
            'administrator' => $language === 'tl' ? 'Admin ka' : 'an administrator',
            default => $language === 'tl' ? 'Guro ka' : 'a teacher',
        };

        if ($language === 'tl') {
            $reply = "Ikaw si {$name}. {$roleLine} sa Tambo National High School.";
            if ($advisory !== '') {
                $reply .= " Ikaw ang adviser ng {$advisory}.";
            }

            return $reply;
        }

        $reply = "You are {$name}, {$roleLine} at Tambo National High School.";
        if ($advisory !== '') {
            $reply .= " Your advisory class is {$advisory}.";
        }

        return $reply;
    }

    private function refusal(string $language, string $role = 'teacher'): string
    {
        if ($language === 'tl') {
            return match ($role) {
                'student' => 'Student portal mo lang ang puwede kong sagutin.',
                'registrar' => 'Registrar portal lang ang puwede kong sagutin.',
                'administrator' => 'Admin portal lang ang puwede kong sagutin.',
                default => 'Teacher portal at mga klase mo lang ang puwede kong sagutin.',
            };
        }

        return match ($role) {
            'student' => 'I can only help with your student portal.',
            'registrar' => 'I can only help with the registrar portal.',
            'administrator' => 'I can only help with the admin portal.',
            default => 'I can only help with your teacher portal and your classes.',
        };
    }

    private function keepOnTopic(string $reply, string $language, string $role): string
    {
        if ($reply === '') {
            return '';
        }

        $phrases = [
            'I can only help with your teacher portal and your classes.',
            'I can only help with the admin portal.',
            'I can only help with your student portal.',
            'I can only help with the registrar portal.',
            'Teacher portal at mga klase mo lang ang puwede kong sagutin.',
            'Admin portal lang ang puwede kong sagutin.',
            'Student portal mo lang ang puwede kong sagutin.',
            'Registrar portal lang ang puwede kong sagutin.',
        ];
        $refused = preg_match('/OFF_TOPIC/i', $reply) === 1;
        foreach ($phrases as $phrase) {
            if (str_contains($reply, $phrase)) {
                $refused = true;
            }
        }

        return $refused ? $this->refusal($language, $role) : $reply;
    }

    private function instructions(string $page, User $teacher, string $language): string
    {
        $where = $page !== ''
            ? "The signed-in person is on this page: {$page}."
            : 'The signed-in person is somewhere in their portal.';
        $records = $this->records->for($teacher);
        $refusal = $this->refusal($language, $teacher->role);
        $guide = $this->records->guide($teacher->role);
        $replyLanguage = $language === 'tl'
            ? 'Reply only in Tagalog. Do not answer in English.'
            : 'Reply only in English.';

        return <<<TEXT
You are the voice assistant for Tambo National High School in Buhi, Camarines Sur.
Only talk about the signed-in person's portal and the records below.
Questions about who they are are part of this portal. Answer with their name from the records.
If the question is about anything else, reply with exactly this sentence and nothing more: {$refusal}
Do not give recipes, news, general knowledge, homework answers, or any other off-topic information.
{$where}

{$records}

When they ask about a count, grade, or attendance mark, answer from the records above.
Earlier chat replies may be wrong. If an earlier reply disagrees with these records, follow the records.
Say the number in the first sentence.
{$replyLanguage}
Use two or three short sentences so the answer can be read aloud.
Do not tell them to open a page when the records already answer the question.
If a fact is not in the records, say it is not in their records.
Do not say you changed a grade, attendance, or any other record.

{$guide}
If they ask to open a page, the portal opens that page. Confirm it in one short sentence.
TEXT;
    }
}
