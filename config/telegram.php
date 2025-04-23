<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    */

    'api_key'      => env('TELEGRAM_BOT_API_KEY'), // Your Telegram Bot API key
    'bot_username' => env('TELEGRAM_BOT_USERNAME'), // Your Telegram Bot username (without @)

    // Define all IDs of admin users
    'admins'       => explode(',', env('TELEGRAM_BOT_ADMINS')), // Your Telegram user IDs in an array

    // Set custom Upload and Download paths
    // 'paths'        => [
    //     'download' => __DIR__ . '/Download',
    //     'upload'   => __DIR__ . '/Upload',
    // ],

    // Requests Limiter (tries to prevent reaching Telegram API limits)
    'limiter'      => [
        'enabled' => true,
    ],
];