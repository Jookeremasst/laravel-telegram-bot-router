<?php

namespace ReyhanTeam\TelegramBotRouter\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetPostRouteCommand extends Command
{
    protected $signature = 'reyhan:set-post-route';

    protected $description = 'Add Telegram webhook POST route to routes/web.php';

    public function handle()
    {
        $webPath = base_path('routes/web.php');

        if (!File::exists($webPath)) {
            $this->error('routes/web.php not found.');
            return;
        }

        $route = "\n\nRoute::post('/telegram/webhook', [\\ReyhanTeam\\TelegramBotRouter\\Core\\UpdateManager::class, 'handleWebhook'])->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->name('telegram.webhook');\n";

        $content = File::get($webPath);

        if (str_contains($content, 'telegram.webhook')) {
            $this->warn('Telegram webhook route already exists.');
            return;
        }

        File::append($webPath, $route);

        $this->info('Telegram webhook route added to routes/web.php ✅');
    }
}