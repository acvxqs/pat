<?php

namespace App\Jobs;

use App\Exceptions\ScraperException;
use App\Services\PlanetarionScraper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseRoundInitialData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $roundNumber;

    public function __construct(int $roundNumber)
    {
        $this->roundNumber = $roundNumber;
    }

    public function handle(): void
    {
        try {
            PlanetarionScraper::parseGovernments($this->roundNumber);
            PlanetarionScraper::parseRaces($this->roundNumber);
            PlanetarionScraper::parseStatsXML($this->roundNumber);
        } catch (ScraperException $e) {
            throw $e; // rethrow to mark the job as failed
        }
    }
}
