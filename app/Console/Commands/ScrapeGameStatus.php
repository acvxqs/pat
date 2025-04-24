<?php

namespace App\Console\Commands;

use App\Exceptions\ScraperException;
use App\Models\Round;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeGameStatus extends Command
{
    protected $signature = 'scrape:game-status';
    protected $description = 'Scrape Planetarion game status and store it in the rounds table.';

    public function handle()
    {
        dispatch_sync(new \App\Jobs\ParseGameStatus());
    }
}