<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Exception\TelegramException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TelegramController extends Controller
{
    public function set()
    {
        try {
            $telegram = new Telegram(Config::get('telegram.api_key'), Config::get('telegram.bot_username'));
            $result = $telegram->setWebhook(Config::get('telegram.webhook.url'));

            if ($result->isOk()) {
                return response()->json(['status' => 'Webhook set successfully']);
            } else {
                return response()->json(['status' => 'Failed to set webhook'], 500);
            }
        } catch (TelegramException $e) {
            Log::error($e->getMessage());
        }
    }
    public function unset()
    {
        try {
            $telegram = new Telegram(Config::get('telegram.api_key'), Config::get('telegram.bot_username'));
            $result = $telegram->deleteWebhook();

            if ($result->isOk()) {
                return response()->json(['status' => 'Webhook unset successfully']);
            } else {
                return response()->json(['status' => 'Failed to unset webhook'], 500);
            }
        } catch (TelegramException $e) {
            Log::error($e->getMessage());
        }
    }
    public function webhook(Request $request)
    {
        try {
            $telegram = new Telegram(Config::get('telegram.api_key'), Config::get('telegram.bot_username'));
            
            $telegram->enableAdmins(Config::get('telegram.admins'));
    
            $telegram->addCommandsPaths(Config::get('telegram.commands.paths'));
            
            $telegram->enableLimiter(Config::get('telegram.limiter'));

            $telegram->handle();

        } catch (TelegramException $e) {
            Log::error($e->getMessage());
        }
    }
}
