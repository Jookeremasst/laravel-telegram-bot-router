<?php

return [
    // حالت اجرا: webhook یا polling
    'mode' => 'polling',

    // تنظیمات polling
    'polling' => [
        // فاصله بین درخواست‌ها (میلی‌ثانیه)
        'interval' => 2000,
        // timeout برای long polling
        'timeout' => 30
    ],

    // تنظیمات webhook
    'webhook' => [
        'endpoint' => '/bot/webhook'
    ]
];