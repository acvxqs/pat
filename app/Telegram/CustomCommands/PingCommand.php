<?php

namespace App\Telegram\CustomCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class PingCommand extends UserCommand
{
    protected $name = 'ping';
    protected $description = 'Simple ping-pong test';
    protected $usage = '/ping';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $chat_id = $this->getMessage()->getChat()->getId();

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => '🏓 Pong!',
        ]);
    }
}
