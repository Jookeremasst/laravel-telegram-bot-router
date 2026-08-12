<?php

namespace ReyhanTeam\TelegramBotRouter;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramRouter
{
    protected Request $request;

    protected TelegramUpdate $update;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle incoming Telegram webhook
     */
    public function handle()
    {
        $content = $this->request->getContent();

        $data = json_decode($content);

        if (! $data || json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON received from Telegram');

            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        if (! isset($data->update_id)) {
            Log::warning('Telegram update without update_id');

            return response()->json(['ok' => true]);
        }

        $this->update = new TelegramUpdate($data);

        TelegramBot::setApplication(app());

        try {
            $this->dispatch($this->update);
        } catch (Throwable $e) {
            Log::error('Telegram Router Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Dispatch update to matching route
     */
    public function dispatch(TelegramUpdate $update): void
    {
        $routes = TelegramBot::getRoutes();
        $fallback = TelegramBot::getFallback();

        foreach ($routes as $route) {

            if (! $this->routeMatches($route, $update)) {
                continue;
            }

            $this->execute($route, $update);

            return;
        }

        if ($fallback) {
            $this->execute([
                'callback' => $fallback,
                'pattern' => 'fallback',
            ], $update);

            return;
        }
        $onInvalid = TelegramBot::getOnInvalid();

        if ($onInvalid) {
            $this->execute([
                'callback' => $onInvalid,
                'pattern' => 'onInvalid',
            ], $update);

            return;
        }
        Log::info('No matching route found', [
            'update_type' => $this->getUpdateType($update),
        ]);
    }

    /**
     * Determine if route matches update
     */
    protected function routeMatches(array $route, TelegramUpdate $update): bool
    {
        $type = $route['type'] ?? null;
        $pattern = $route['pattern'] ?? null;

        switch ($type) {

            case 'callback_query':

                if (! isset($update->callback_query)) {
                    return false;
                }

                $data = $update->callback_query->data ?? '';

                return $this->matchPattern($pattern, $data, $update);

            case 'command':

                if (! isset($update->message->text)) {
                    return false;
                }

                $text = trim($update->message->text);

                if (! str_starts_with($text, '/')) {
                    return false;
                }

                $command = explode(' ', $text)[0];

                if (str_contains($command, '@')) {
                    $command = explode('@', $command)[0];
                }

                return $command === $pattern;

            case 'text':

                if (! isset($update->message->text)) {
                    return false;
                }

                $text = trim($update->message->text);

                return $this->matchPattern($pattern, $text, $update);
        }

        return false;
    }

    /**
     * Match plain text or regex pattern
     */
    protected function matchPattern(string $pattern, string $text, TelegramUpdate $update): bool
    {
        $pattern = trim($pattern);
        $text = trim($text);

        if ($this->isRegex($pattern)) {

            if (preg_match($pattern, $text, $matches)) {

                $update->matches = $matches;

                return true;
            }

            return false;
        }

        return $pattern === $text;
    }

    /**
     * Detect if pattern is regex
     */
    protected function isRegex(string $pattern): bool
    {
        if (strlen($pattern) < 3) {
            return false;
        }

        $delimiter = $pattern[0];

        return $delimiter === substr($pattern, -1)
            && ! ctype_alnum($delimiter)
            && $delimiter !== '\\';
    }

    /**
     * Execute route action
     */
    protected function execute(array $route, TelegramUpdate $update): void
    {
    $callback = $route['callback'];

    try {
        // اجرای مستقیم callback بدون واسطه‌ی Pipeline
        $this->resolveAction($callback, $update);

    } catch (Throwable $e) {
        Log::error('Route execution failed', [
            'pattern' => $route['pattern'] ?? 'fallback',
            'error' => $e->getMessage(),
        ]);

        throw $e;
    }
}

    /**
     * Resolve route action
     *
     * Supports:
     * - Closure
     * - [Controller::class, 'method']
     */
    protected function resolveAction($action, TelegramUpdate $update)
    {
        if ($action instanceof \Closure) {
            return $action($update);
        }

        if (is_array($action) && count($action) === 2) {
            [$controller, $method] = $action;

            // --- کد دیباگ زیر را اضافه کن ---
            if (! class_exists($controller)) {
                Log::error('TelegramRouter Error: Controller class does not exist', [
                    'provided_controller' => $controller,
                    'action_array' => $action,
                ]);

                // اگر می‌خواهی همان لحظه ببینی مشکل کجاست:
                throw new \Exception("کلاس کنترلر با نام [{$controller}] یافت نشد. چک کن در routes/bot.php چی نوشتی!");
            }
            // -------------------------------

            $instance = app()->make($controller);

            return app()->call([$instance, $method], [
                'update' => $update,
            ]);
        }

        throw new \InvalidArgumentException('Invalid route action provided.');
    }

    /**
     * Detect update type
     */
    protected function getUpdateType(TelegramUpdate $update): string
    {
        if (isset($update->callback_query)) {
            return 'callback_query';
        }

        if (isset($update->message->text)) {

            return str_starts_with($update->message->text, '/')
                ? 'command'
                : 'text';
        }

        return 'unknown';
    }

   
}
