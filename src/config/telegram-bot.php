<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    */

    'token' => env('TELEGRAM_BOT_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Mode: webhook or polling
    |--------------------------------------------------------------------------
    |
    | Possible values:
    |   - 'webhook'
    |   - 'polling'
    |
    */

    'mode' => env('TELEGRAM_BOT_MODE', 'webhook'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */

    'webhook' => [

        // Your webhook endpoint inside Laravel
        'path' => '/telegram/webhook',

        // Set webhook URL in Telegram
        'url' => env('TELEGRAM_WEBHOOK_URL', ''),

    ],

    /*
    |--------------------------------------------------------------------------
    | Polling Settings
    |--------------------------------------------------------------------------
    */

    'polling' => [

        // Interval between polling requests (ms)
        'interval' => 1500,

        // Long polling timeout (seconds)
        'timeout' => 30,
    ],

    

];
