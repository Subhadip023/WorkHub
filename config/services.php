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

    'gemini' => [
        // Never commit the key: set GEMINI_API_KEY in .env only.
        'key' => env('GEMINI_API_KEY'),
        // Check the current stable "flash" model ID at https://ai.google.dev/gemini-api/docs/models
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 20),
    ],

    'external_api' => [
        // Accept signatures without X-Api-Timestamp (no replay protection).
        // Keep true only while integrations migrate; set false in production afterwards.
        'allow_legacy_signatures' => (bool) env('EXTERNAL_API_ALLOW_LEGACY_SIGNATURES', true),
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

    'github' => [
        'pat' => env('GITHUB_PAT'),
        'owner' => env('GITHUB_OWNER', 'Subhadip023'),
        'repo' => env('GITHUB_REPO', 'WorkHub'),
    ],

    'task_api' => [
        'url' => env('WORKHUB_TASK_API_URL'),
        'key' => env('WORKHUB_TASK_API_KEY'),
        'secret' => env('WORKHUB_TASK_API_SECRET'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'skip' => env('RECAPTCHA_SKIP', false),
    ],

];
