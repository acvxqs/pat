<?php

namespace App\Jobs;

use App\Exceptions\ScraperException;
use App\Services\PlanetarionScraper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseGameStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        try {
            PlanetarionScraper::parseGameStatus();
        } catch (ScraperException $e) {
            throw $e; // rethrow to mark the job as failed
        }
    }
}
