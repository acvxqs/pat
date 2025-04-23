<?php

namespace App\Exceptions;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request;

class ScraperException extends Exception
{
    public const GAMESTATUS = 1000;
    public const GOVERNMENT = 1001;
    public const RACES      = 1002;
    public const STATSXML   = 1003;

    public int $round;

    public function __construct(string $message, int $round, int $code = 0, ?\Throwable $previous = null)
    {
        $this->round = $round;

        $fullMessage = "❌ Scraper Error (Round $round): $message";

        parent::__construct($fullMessage, $code, $previous);

        Log::error("[$code] $fullMessage");

        if ($previous) {
            Log::debug($previous->getMessage() . "\n" . $previous->getTraceAsString());
        }

        if ($channelId = Setting::get('home_channel')) {
            Request::sendMessage([
                'chat_id' => $channelId,
                'text' => $fullMessage,
            ]);
        }
    }
}
