<?php

namespace ReyhanTeam\TelegramBotRouter\Console;

use Illuminate\Console\Command;
use ReyhanTeam\TelegramBotRouter\Core\UpdateManager;

class StartPollingCommand extends Command
{
    protected $signature = 'reyhan:start-polling';
    protected $description = 'Start Telegram bot polling loop';

    public function handle(): int
    {
        $mode = config('telegram-bot.mode');

        if ($mode !== 'polling') {
            $this->newLine();
            $this->error('Cannot start polling.');
            $this->line("Current Telegram bot mode: <fg=yellow>{$mode}</>");
            $this->line('Polling requires the mode to be set to <fg=green>polling</>.');
            $this->newLine();
            $this->line('Change this value in <fg=cyan>config/telegram-bot.php</>:');
            $this->line("  'mode' => 'polling',");
            $this->newLine();

            return self::FAILURE;
        }

        $this->info('Polling started... (Ctrl+C to stop)');

        $manager = new UpdateManager();
        $manager->startPolling();

        return self::SUCCESS;
    }
}
