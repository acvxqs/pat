<?php

namespace App\Exceptions;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request;

class ScraperException extends Exception
{
    public const GENERIC    = 0;    // Generic error
    public const GAMESTATUS = 1000; // Runs every minute
    public const GOVERNMENT = 1001; // Only runs when new round is created
    public const RACES      = 1002; // Only runs when new round is created
    public const STATSXML   = 1003; // Only runs when new round is created
    public const PLANETS    = 1004; // Only runs when current_tick wasChanged
    public const GALAXIES   = 1005; // Only runs when current_tick wasChanged
    public const ALLIANCES  = 1006; // Only runs when current_tick wasChanged
    public const USERFEED   = 1007; // Only runs when current_tick wasChanged

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

        if ($channelId = Setting::get('tech_channel')) {
            Request::sendMessage([
                'chat_id' => $channelId,
                'text' => $fullMessage,
            ]);
        }
    }
}
