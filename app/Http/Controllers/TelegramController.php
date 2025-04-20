<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Longman\TelegramBot\Telegram;

class TelegramController extends Controller
{
    public function set()
    {
        $telegram = new Telegram(config('telegram.api_key'), config('telegram.bot_username'));
        $result = $telegram->setWebhook(config('telegram.webhook.url'));

        if ($result->isOk()) {
            return response()->json(['status' => 'Webhook set successfully']);
        } else {
            return response()->json(['status' => 'Failed to set webhook'], 500);
        }
    }
    public function unset()
    {
        $telegram = new Telegram(config('telegram.api_key'), config('telegram.bot_username'));
        $result = $telegram->deleteWebhook();

        if ($result->isOk()) {
            return response()->json(['status' => 'Webhook unset successfully']);
        } else {
            return response()->json(['status' => 'Failed to unset webhook'], 500);
        }
    }
    public function delete()
    {
        $telegram = new Telegram(config('telegram.api_key'), config('telegram.bot_username'));
        $result = $telegram->deleteWebhook();

        if ($result->isOk()) {
            return response()->json(['status' => 'Webhook deleted successfully']);
        } else {
            return response()->json(['status' => 'Failed to delete webhook'], 500);
        }
    }
    public function hook(Request $request)
    {
        $telegram = new Telegram(config('telegram.api_key'), config('telegram.bot_username'));
        $telegram->handle();

        return response()->json(['status' => 'Webhook handled successfully']);
    }
    public function updates()
    {
        $telegram = new Telegram(config('telegram.api_key'), config('telegram.bot_username'));
        $updates = $telegram->getWebhookUpdates();

        return response()->json($updates);
    }
}
