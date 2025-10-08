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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'simpels' => [
        'api_url' => env('SIMPELS_API_URL', 'http://localhost:8000/api/epos'),
        'timeout' => env('SIMPELS_API_TIMEOUT', 30),
        'api_key' => env('SIMPELS_API_KEY'),
        'endpoints' => [
            'santri_all' => '/santri/all',
            'guru_all' => '/guru/all',
            'santri_rfid' => '/santri/rfid',
            'guru_rfid' => '/guru/rfid',
            'limit_summary' => '/limit/summary',
            'santri_deduct' => '/santri/{id}/deduct',
            'santri_refund' => '/santri/{id}/refund',
            'transaction_sync' => '/transaction/sync',
            'santri_transactions' => '/santri/transactions',
            'daily_spending' => '/santri/daily-spending',
            'balance_topup' => '/balance/topup',
        ],
    ],

];
