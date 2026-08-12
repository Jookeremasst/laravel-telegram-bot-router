<?php

namespace ReyhanTeam\TelegramBotRouter\Console;

use Illuminate\Console\Command;
use ReyhanTeam\TelegramBotRouter\Core\UpdateManager;

class StartPollingCommand extends Command
{
    protected $signature = 'reyhan:start-polling';
    protected $description = 'Start Telegram bot polling loop';

    public function handle()
    {
        $this->info("Polling started... (Ctrl+C to stop)");

        $manager = new UpdateManager();
        $manager->startPolling();
    }
}