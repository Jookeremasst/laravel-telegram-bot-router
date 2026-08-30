<?php

namespace ReyhanTeam\TelegramBotRouter;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReyhanTeam\TelegramBotRouter\Console\SetPostRouteCommand;
use ReyhanTeam\TelegramBotRouter\Core\UpdateManager;

class TelegramRouterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/telegram-bot-router.php',
            'telegram-bot-router'
        );

        $this->app->singleton(TelegramRouter::class, function ($app) {
            return new TelegramRouter($app['request']);
        });

        $this->app->alias(TelegramRouter::class, 'telegram.router');

        $this->commands([
            \ReyhanTeam\TelegramBotRouter\Console\StartPollingCommand::class,
        ]);
    }

    public function boot(): void
    {
        $this->loadBotRoutes();

        if (config('telegram-bot-router.mode') === 'webhook') {
            Route::post(
                config('telegram-bot-router.webhook.path'),
                [UpdateManager::class, 'handleWebhook']
            );
        }

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->publishResources();
        }
    }

    protected function loadBotRoutes(): void
    {
        $appRoutes = base_path('routes/bot.php');
        $packageRoutes = __DIR__ . '/../routes/bot.php';

        if (file_exists($appRoutes)) {
            require $appRoutes;
            return;
        }

        if (file_exists($packageRoutes)) {
            require $packageRoutes;
        }
    }

    protected function registerCommands(): void
    {
        $this->commands([
            SetPostRouteCommand::class,
        ]);
    }

    protected function publishResources(): void
    {
        $this->publishes([
            __DIR__ . '/config/telegram-bot-router.php' => config_path('telegram-bot-router.php'),
        ], 'telegram-bot-config');

        $this->publishes([
            __DIR__ . '/../routes/bot.php' => base_path('routes/bot.php'),
        ], 'telegram-bot-routes');
    }
}
