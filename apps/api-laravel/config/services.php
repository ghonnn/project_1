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

    'freeradius' => [
        'host' => env('FREERADIUS_HOST', '10.20.1.19'),
        'auth_port' => env('FREERADIUS_AUTH_PORT', 1812),
        'acct_port' => env('FREERADIUS_ACCT_PORT', 1813),
        'test_secret' => env('FREERADIUS_TEST_SECRET', 'testing123'),
        'sync_mode' => env('FREERADIUS_SYNC_MODE', 'simulated'),
    ],

    'mikrotik' => [
        'test_public_ip' => env('MIKROTIK_TEST_PUBLIC_IP', '103.142.202.226'),
    ],

];
