<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'transcription_model' => env('GROQ_TRANSCRIPTION_MODEL', 'whisper-large-v3-turbo'),
        // Short utterances (a single grade like "eighty five") do not need
        // large-v3's extra accuracy — turbo returns the digits ~3x faster.
        'transcription_grade_model' => env(
            'GROQ_TRANSCRIPTION_GRADE_MODEL',
            'whisper-large-v3-turbo',
        ),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'chat_model' => env('GROQ_CHAT_MODEL', 'openai/gpt-oss-20b'),
    ],

    'elevenlabs' => [
        'key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM'),
        'model_id' => env('ELEVENLABS_MODEL_ID', 'eleven_multilingual_v2'),
        'flash_model' => env('ELEVENLABS_FLASH_MODEL', 'eleven_flash_v2_5'),
        'base_url' => env('ELEVENLABS_BASE_URL', 'https://api.elevenlabs.io/v1'),
    ],

];
