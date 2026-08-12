<?php

namespace ReyhanTeam\TelegramBotRouter\Providers;

use Illuminate\Support\Facades\Log;
use ReyhanTeam\TelegramBotRouter\TelegramUpdate;

class PollingProvider
{
    protected $router;
    protected $interval;
    protected $timeout;

    public function __construct($router, $config)
    {
        $this->router   = $router;
        $this->interval = $config['polling']['interval'] ?? 2000;
        $this->timeout  = $config['polling']['timeout'] ?? 30;
    }

    public function start()
    {
        $offset = 0;

        while (true) {

            $updates = $this->getUpdates($offset);

            foreach ($updates as $update) {
                echo "new update recived: " . $update['update_id'] . PHP_EOL;

                $offset = $update['update_id'] + 1;

                $tu = TelegramUpdate::fromArray($update);

                $this->router->dispatch($tu);
            }

            usleep($this->interval * 1000);
        }
    }

    private function getUpdates($offset)
    {
        $token = getenv('BOT_TOKEN');

        $url = getenv('BOT_PATH_POLLING_URL')
            . $token
            . "/getUpdates?offset={$offset}&timeout={$this->timeout}";

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => $this->timeout + 10,
            CURLOPT_HTTPGET => true,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);

            curl_close($ch);

            throw new \Exception(
                'Telegram connection error: ' . $error
            );
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception(
                "Telegram API returned HTTP {$httpCode}: {$response}"
            );
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new \Exception(
                'Invalid response from Telegram API: ' . $response
            );
        }

        return $data['result'] ?? [];
    }
}