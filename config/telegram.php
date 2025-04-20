<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    */

    'api_key'      => env('TELEGRAM_BOT_API_KEY', ''), // Your Telegram Bot API key
    'bot_username' => env('TELEGRAM_BOT_USERNAME', ''), // Your Telegram Bot username (without @)

    'webhook'      => [
        'url' => env('TELEGRAM_BOT_WEBHOOK_URL'), // Your webhook URL (optional)
        // Use self-signed certificate
        // 'certificate'     => __DIR__ . '/path/to/your/certificate.crt',
        // Limit maximum number of connections
        // 'max_connections' => 5,
    ],

    'commands'     => [
        // Define all paths for your custom commands
        // DO NOT PUT THE COMMAND FOLDER THERE. IT WILL NOT WORK. 
        // Copy each needed Commandfile into the CustomCommand folder and uncommend the Line below
        'paths'   => [
            base_path('/app/Telegram/CustomCommands'),
        ],
        // Here you can set any command-specific parameters
        'configs' => [
            // - Google geocode/timezone API key for /date command (see DateCommand.php)
            // 'date'    => ['google_api_key' => 'your_google_api_key_here'],
            // - OpenWeatherMap.org API key for /weather command (see WeatherCommand.php)
            // 'weather' => ['owm_api_key' => 'your_owm_api_key_here'],
            // - Payment Provider Token for /payment command (see Payments/PaymentCommand.php)
            // 'payment' => ['payment_provider_token' => 'your_payment_provider_token_here'],
        ],
    ],

    // Define all IDs of admin users
    'admins'       => explode(',', env('TELEGRAM_BOT_ADMINS', '')), // Your Telegram user IDs (comma separated)

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