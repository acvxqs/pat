<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScrapeGameStatus extends Command
{
    protected $signature = 'scrape:game-status';
    protected $description = 'Scrape Planetarion game status and store it in the rounds table.';

    public function handle()
    {
        dispatch_sync(new \App\Jobs\ParseGameStatus());
    }
}