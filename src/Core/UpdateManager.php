<?php

namespace ReyhanTeam\TelegramBotRouter\Core;

use ReyhanTeam\TelegramBotRouter\Providers\WebhookProvider;
use ReyhanTeam\TelegramBotRouter\Providers\PollingProvider;

use function Illuminate\Log\log;
use Illuminate\Support\Facades\Log;

class UpdateManager
{
    /**
     * هندل کردن درخواست‌های webhook از سمت تلگرام
     */
    public function handleWebhook()
    {
        $mode = config('telegram-bot.mode');

        if ($mode !== 'webhook') {
            // اگر مود webhook نیست، باید 403 بدهیم
            return response()->json(['error' => 'Webhook is disabled (current mode: '.$mode.')'], 403);
        }

        $router = app('telegram.router');
        $config = config('telegram-bot');

        // طبق طراحی فعلی، WebhookProvider خودش php://input را می‌خواند
        $provider = new WebhookProvider($router, $config);

        // در این مدل نیازی به request() نیست
        $provider->start();

        return response()->json(['status' => 'ok']);
    }

    /**
     * شروع polling loop از طریق دستور آرتیسان
     */
    public function startPolling()
    {
        $mode = config('telegram-bot.mode');

        if ($mode !== 'polling') {
            throw new \Exception("Polling mode is disabled. Current mode: {$mode}");
        }

        $router = app('telegram.router');
        $config = config('telegram-bot');

        $provider = new PollingProvider($router, $config);
        $provider->start();
    }
}