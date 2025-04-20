<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\TelegramController;

Route::group(['prefix' => Config::get('telegram.api_key', '')], function () {
    Route::get('/set', [TelegramController::class, 'set']);
    Route::get('/unset', [TelegramController::class, 'unset']);
    Route::post('/webhook', [TelegramController::class, 'webhook']);
});