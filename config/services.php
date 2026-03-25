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

    'africastalking' => [
        'username' => env('AFRICAS_TALKING_USERNAME'),
        'api_key' => env('AFRICAS_TALKING_API_KEY'),
        'real_send' => env('AFRICAS_TALKING_REAL_SEND', false), // Default to false for better dev experience
    ],

    'yengapay' => [
        'api_key' => env('YENGAPAY_API_KEY'),
        'organization_id' => env('YENGAPAY_ORGANIZATION_ID'),
        'project_id' => env('YENGAPAY_PROJECT_ID'),
        'base_url' => env('YENGAPAY_BASE_URL'),
        'webhook_secret' => env('YENGAPAY_WEBHOOK_SECRET'),
    ],

];
