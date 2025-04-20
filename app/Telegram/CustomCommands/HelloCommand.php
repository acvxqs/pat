<?php

namespace App\Telegram\CustomCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class HelloCommand extends UserCommand
{
    protected $name = 'hello';
    protected $description = 'Say hello';
    protected $usage = '/hello';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $chat_id = $this->getMessage()->getChat()->getId();

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => 'Hello from Laravel Telegram Bot! 👋',
        ]);
    }
}
