<?php

namespace ReyhanTeam\TelegramBotRouter;

use Illuminate\Contracts\Container\Container;

/**
 * TelegramBot Facade-like router for defining Telegram update routes.
 *
 * Supports Laravel-style routing for Telegram bot updates:
 * - Text messages
 * - Commands
 * - Callback queries
 * - Fallback handlers
 *
 * Example:
 *
 * TelegramBot::onText('salam', [SalamController::class, 'salam']);
 * TelegramBot::onCommand('start', function ($update) { ... });
 * TelegramBot::onCallbackQuery(function ($update) { ... });
 * TelegramBot::fallback(function ($update) { ... });
 */
class TelegramBot
{
    protected static $onInvalidAction = null;

    
    /**
     * The Laravel application instance.
     */
    protected static ?Container $app = null;

    /**
     * All registered routes for Telegram updates.
     *
     * @var array<int, array{type: string, pattern: ?string, callback: callable}>
     */
    protected static array $routes = [];

    /**
     * The fallback callback for unmatched updates.
     *
     * @var callable|null
     */
    protected static $fallback = null;

    // ────────────────────────────────────────────────
    //  ROUTE DEFINITIONS
    // ────────────────────────────────────────────────

    /**
     * Register a command handler, e.g. "/start" or "/help".
     *
     * @param  callable  $callback
     */
    public static function onCommand(string $command, $callback)
    {
        $command = '/'.ltrim($command, '/');

        static::addRoute('command', $command, $callback);
    }

    /**
     * Register a plain text message handler.
     * Supports exact text or regex patterns (e.g. "/^hi/i").
     *
     * @param  callable  $callback
     */
    public static function onText(string $pattern, $callback)
    {
        static::addRoute('text', $pattern, $callback);
    }

    /**
     * Register a callback_query handler (button presses etc.)
     *
     * @param  callable  $callback
     */
    public static function onCallbackQuery($callback)
    {
        static::addRoute('callback_query', null, $callback);
    }

    /**
     * Define fallback handler — executed when no route matches the update.
     *
     * @param  callable  $callback
     */
    public static function fallback($callback): void
    {
        static::$fallback = $callback;
    }

    // ────────────────────────────────────────────────
    //  CORE LOGIC
    // ────────────────────────────────────────────────

    /**
     * Add a route record to the internal collection.
     *
     * @param  string  $type  Route type (command, text, callback_query)
     * @param  string|null  $pattern  Matching pattern
     * @param  callable  $callback  Callable/Closure/controller action
     */
    protected static function addRoute(string $type, ?string $pattern, $callback)
    {
        static::$routes[] = [
            'type' => $type,
            'pattern' => $pattern,
            'callback' => $callback,
        ];

        
    }

    // ────────────────────────────────────────────────
    //  ACCESSORS
    // ────────────────────────────────────────────────

    /**
     * Get all defined Telegram routes.
     *
     * @return array<int, array{type: string, pattern: ?string, callback: callable}>
     */
    public static function getRoutes(): array
    {
        return static::$routes;
    }

    /**
     * Get the fallback handler if defined.
     */
    public static function getFallback(): ?callable
    {
        return static::$fallback;
    }

    /**
     * Set the Laravel application container for dependency injection.
     */
    public static function setApplication(Container $app): void
    {
        static::$app = $app;
    }

    /**
     * Get the Laravel application container.
     */
    public static function getApplication(): ?Container
    {
        return static::$app;
    }

    public static function onInvalid($callback): void
    {
        self::$onInvalidAction = $callback;
    }

    public static function getOnInvalid()
    {
        return self::$onInvalidAction;
    }

    
}
