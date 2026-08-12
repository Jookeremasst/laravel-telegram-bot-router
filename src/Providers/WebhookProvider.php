<?php
namespace ReyhanTeam\TelegramBotRouter\Providers;

use Illuminate\Support\Facades\Log;
use ReyhanTeam\TelegramBotRouter\TelegramUpdate;

class WebhookProvider
{
    protected $router;

    public function __construct($router, $config)
    {
        $this->router = $router;
    }

    public function start()
    {
        $update = json_decode(file_get_contents('php://input'), true);

        if ($update) {
            $update = TelegramUpdate::fromArray($update);
            $this->router->dispatch($update);
        }
    }
}