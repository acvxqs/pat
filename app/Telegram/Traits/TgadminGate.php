<?php

namespace App\Telegram\Traits;

trait TgadminGate
{
    public function abortIfNotBotAdmin(): ?ServerResponse
    {
        $telegram_id = $this->getMessage()->getFrom()->getId();
        $chat_id = $this->getMessage()->getChat()->getId();
        $admin_ids = Config::get('telegram.admins', []);

        if (!in_array($telegram_id, $admin_ids)) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => "⛔ This command is restricted to bot administrators.",
            ]);
        }

        return null; // Passes
    }
}
