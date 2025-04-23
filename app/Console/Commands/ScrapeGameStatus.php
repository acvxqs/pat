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
        try {
            $client = new Client();
            $url = Config::get('planetarion.status_main_game');
            $html = (string) $client->get($url)->getBody();
            $crawler = new Crawler($html);

            $textBlock = $crawler->filter('#game_status')->text();

            preg_match('/Round (\d+)\s*-\s*(.+)/', $textBlock, $m);
            $roundNumber = (int) $m[1] ?? null;
            $roundName = trim($m[2] ?? '');

            preg_match('/Current Tick:\s*(\d+)/', $textBlock, $m);
            $currentTick = isset($m[1]) ? (int) $m[1] : null;

            preg_match('/Last Tick Happened At (\d{2}:\d{2}) (\w+) on (.+)/', $textBlock, $m);
            $lastTickAt = null;
            if (isset($m[1], $m[2], $m[3])) {
                $tz = new \DateTimeZone($m[2]);
                $lastTickAt = Carbon::createFromFormat('H:i l jS F Y', "$m[1] $m[3]", $tz);
            }

            $fields = [
                'tick_speed' => self::extractSeconds($textBlock, '/Tick Speed:\s*(\d+)\s*(minute|hour)s?/i'),
                'ticking' => self::extractBoolean($textBlock, '/Ticking:\s*(Yes|No)/i'),
                'max_membercount' => self::extractInt($textBlock, '/Max Membercount:\s*(\d+)/i'),
                'members_counting_towards_alliance_score' => self::extractInt($textBlock, '/Members Counting Towards Alliance Score:\s*(\d+)/i'),
                'xp_per_tick_defending_universe' => self::extractInt($textBlock, '/XP\/Tick Defending Universe:\s*(\d+)/i'),
                'xp_per_tick_defending_galaxy' => self::extractInt($textBlock, '/XP\/Tick Defending Galaxy:\s*(\d+)/i'),
                'xp_landing_defense' => self::extractInt($textBlock, '/XP Landing Defense:\s*(\d+)/i'),
                'max_cap' => self::extractInt($textBlock, '/Max Cap:\s*(\d+)%/i'),
                'max_structures_destroyed' => self::extractInt($textBlock, '/Max Structures Destroyed:\s*(\d+)%/i'),
                'salvage_from_attacking_ships' => self::extractInt($textBlock, '/Salvage From Attacking Ships:\s*(\d+)%/i'),
                'salvage_from_defending_ships' => self::extractInt($textBlock, '/Salvage From Defending Ships:\s*(\d+)%/i'),
                'asteroid_armor' => self::extractInt($textBlock, '/Asteroid Armor:\s*(\d+)/i'),
                'construction_armor' => self::extractInt($textBlock, '/Construction Armor:\s*(\d+)/i'),
                'damage_done_on_primary_target' => self::extractInt($textBlock, '/Damage Done On Primary Target:\s*(\d+)%/i'),
                'damage_done_on_secondary_target' => self::extractInt($textBlock, '/Damage Done On Secondary Target:\s*(\d+)%/i'),
                'damage_done_on_tertiary_target' => self::extractInt($textBlock, '/Damage Done On Tertiary Target:\s*(\d+)%/i'),
                'pods_die_when_capping' => self::extractBoolean($textBlock, '/Pods Die When Capping:\s*(Yes|No)/i'),
                'structure_killers_die' => self::extractBoolean($textBlock, '/Structure Killers Die:\s*(Yes|No)/i'),
                'stealship_steal_die_ratio' => self::extractInt($textBlock, '/Stealship Steal\/Die Ratio:\s*(\d+)%/i')
            ];

            if ($roundNumber && $roundName) {
                Round::updateOrCreate(
                    ['number' => $roundNumber],
                    array_merge($fields, [
                        'name' => $roundName,
                        'current_tick' => $currentTick,
                        'last_tick_happened_at' => $lastTickAt,
                    ])
                );
                $this->info("Round $roundNumber updated.");
            } else {
                $this->error("Failed to extract round number or name.");
            }

        } catch (\Throwable $e) {
            $this->error("Scraping error: " . $e->getMessage());
            throw new ScraperException('Failed to parse game status', $roundNumber, ScraperException::GAMESTATUS, $e);
        }
    }

    private static function extractInt($text, $regex): ?int
    {
        return preg_match($regex, $text, $m) ? (int) $m[1] : null;
    }

    private static function extractBoolean($text, $regex): ?bool
{
    if (preg_match($regex, $text, $m)) {
        $value = strtolower($m[1]);

        if ($value === 'yes') {
            return true;
        }
        return false;
    }
    return null;
}

    private static function extractSeconds($text, $regex): ?int
    {
        if (preg_match($regex, $text, $m)) {
            $value = (int) $m[1];
            $unit = strtolower($m[2]);
            return $unit === 'hour' ? $value * 3600 : $value * 60;
        }
        return null;
    }
}
