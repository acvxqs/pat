<?php

namespace App\Telegram\CustomCommands\Tgadmins;

use App\Models\Setting;
use App\Telegram\Traits\TgadminGate;
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class TgadminCommand extends AdminCommand
{
    protected $name = 'tgadmin';
    protected $description = 'Manage Telegram bot admin settings';
    protected $usage = '/tgadmin <set|get> <home|scan|admin>';
    protected $version = '1.0.1';
    protected $private_only = false;

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $args = explode(' ', trim($message->getText(true)));

        if (count($args) !== 2) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "❗ Usage: /tgadmin <set|get> <home|scan|admin>",
            ]);
        }

        [$action, $key] = $args;
        $action = strtolower($action);
        $key = strtolower($key);
        
        $validActions = ['set', 'get'];
        if (!in_array($action, $validActions)) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "❗ Invalid action: `$action`. Use `set` or `get`.",
                'parse_mode' => 'Markdown',
            ]);
        }

        $validKeys = ['home', 'scan', 'admin'];

        if (!in_array($key, $validKeys)) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "❗ Invalid key: `$key`. Use one of: home, scan, admin.",
                'parse_mode' => 'Markdown',
            ]);
        }

        $settingKey = "{$key}_channel";

        if ($action === 'set') {
            Setting::set($settingKey, $chat_id);

            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "✅ This chat has been set as the *$key* channel.",
                'parse_mode' => 'Markdown',
            ]);
        }

        if ($action === 'get') {
            $value = Setting::get($settingKey);
        
            if (!$value) {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "⚠️ No {$key} channel is currently set.",
                ]);
            }
        
            $isCurrent = ((string) $value === (string) $chat_id);
        
            $message = "📍 The *{$key}* channel ID is:\n`{$value}`\n\n";
        
            $message .= $isCurrent
                ? "✅ This chat is the *configured {$key} channel*."
                : "ℹ️ This chat is *not* the configured {$key} channel.";
        
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        }

        // fallback
        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => "❗ Unknown action: `$action`. Use `set` or `get`.",
            'parse_mode' => 'Markdown',
        ]);
    }
}
