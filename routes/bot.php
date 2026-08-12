<?php

use Illuminate\Support\Facades\Route;
use ReyhanTeam\TelegramBotRouter\TelegramBot as BOT;
use Telegram\Bot\Laravel\Facades\Telegram;
/*
|--------------------------------------------------------------------------
| Telegram Bot Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your Telegram bot. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "bot" middleware group. Enjoy building your bot!
|
*/

BOT::onCommand('start', function ($update) {
    return Telegram::sendMessage([
        'chat_id' => $update->message->chat->id,
        'text' => 'Welcome! Type /help to see available commands.',
    ]);
});
