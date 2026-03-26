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
        'api_url' => env('SIMPELS_API_URL', 'http://localhost:8001/api'),
        // SIMPELS_API_URL harus diset ke: http://<host>/api (tanpa suffix /v1/wallets)
        'timeout' => env('SIMPELS_API_TIMEOUT', 30),
        'api_key' => env('SIMPELS_API_KEY'),
        'endpoints' => [
            // Health check
            'ping' => '/v1/wallets/ping',

            // RFID lookup
            'rfid_lookup' => '/v1/wallets/rfid/uid', // GET /v1/wallets/rfid/uid/{uid}

            // EPOS transaction (deduct wallet)
            'epos_transaction' => '/v1/wallets/epos/transaction',

            // Pesanan Kebutuhan
            'kebutuhan_order'        => '/v1/epos/kebutuhan-order',
            'kebutuhan_order_status' => '/v1/epos/kebutuhan-order/santri',

            // Withdrawal
            'withdrawal_create' => '/v1/wallets/epos/withdrawal',
            'withdrawal_status' => '/v1/wallets/epos/withdrawal',

            // Legacy endpoints
            'santri_all'         => '/v1/wallets/santri/all',
            'guru_all'           => '/v1/wallets/guru/all',
            'santri_rfid'        => '/v1/wallets/santri/rfid',
            'guru_rfid'          => '/v1/wallets/guru/rfid',
            'limit_summary'      => '/v1/wallets/ping',
            'santri_transactions' => '/v1/wallets/santri/transactions',
            'daily_spending'     => '/v1/wallets/santri/daily-spending',
            'balance_topup'      => '/v1/wallets/balance/topup',
        ],
    ],

    'epos_webhook' => [
        'secret' => env('EPOS_WEBHOOK_SECRET'),
    ],

];
