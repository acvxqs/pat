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
            $url = secure_url(Config::get('telegram.api_key') . '/webhook');
            $result = $telegram->setWebhook($url, [
                'allowed_updates' => ['message', 'chat_member'],
            ]);

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
    
            $telegram->addCommandsPaths([
                app_path('Telegram/CustomCommands'),
            ]);
            
            $telegram->enableLimiter(Config::get('telegram.limiter'));

            Log::info('Loaded commands:', $telegram->getCommandsList());
            
            $telegram->handle();

        } catch (TelegramException $e) {
            Log::error($e->getMessage());
        }
    }
}
