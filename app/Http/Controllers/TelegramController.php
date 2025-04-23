<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

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
                app_path('Telegram/CustomCommands/Tgadmins'),
            ]);
            
            $telegram->enableLimiter(Config::get('telegram.limiter'));

            Log::info('Loaded commands:', $telegram->getCommandsList());
            
            $telegram->handle();

        } catch (TelegramException $e) {
            Log::error($e->getMessage());
        }
    }
}
