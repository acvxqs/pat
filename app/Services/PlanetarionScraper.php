<?php

namespace App\Services;

use App\Exceptions\ScraperException;
use App\Models\Eta;
use App\Models\Race;
use App\Models\Round;
use App\Models\RoundShipData;
use App\Models\Ship;
use App\Models\UnitClass;
use App\Models\WeaponType;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Symfony\Component\DomCrawler\Crawler;

class PlanetarionScraper
{
    public static function parseGameStatus(): void
    {
        try {
            $client = new Client();
            $url = Config::get('planetarion.status_main_game');
            $html = (string) $client->get($url)->getBody();
            $crawler = new Crawler($html);
        
            $textBlock = $crawler->filter('#game_status')->text();
        
            if (!preg_match('/Round (\d+)\s*-\s*(.+)/i', $textBlock, $m)) {
                throw new ScraperException('Failed to match round number and name', 0, ScraperException::GAMESTATUS);
            }

            $roundNumber = (int) $m[1];
            $roundName = trim($m[2]);
        
            $currentTick = self::extractInt($textBlock, '/Current Tick:\s*(\d+)/i', $roundNumber, ScraperException::GAMESTATUS);

            if (!preg_match('/Last Tick Happened At (\d{2}:\d{2}) (\w+) on (.+)/i', $textBlock, $m)) {
                throw new ScraperException('Failed to match last tick timestamp', $roundNumber, ScraperException::GAMESTATUS);
            }
        
            try {
                $time = $m[1] ?? null;  // e.g., "12:45"
                $timezone = $m[2] ?? null; // e.g., "GMT"
                $date = $m[3] ?? null;  // e.g., "Thursday 4th April 2025"
            
                if (!$time || !$timezone || !$date) {
                    throw new ScraperException("Missing time, timezone, or date components.", $roundNumber, ScraperException::GAMESTATUS);
                }
            
                // Optional: sanity check that $date contains expected elements
                if (!preg_match('/\b\d{1,2}(st|nd|rd|th)\b/i', $date)) {
                    throw new ScraperException("Date string doesn't contain an ordinal day: '{$date}'", $roundNumber, ScraperException::GAMESTATUS);
                }
            
                // Optional: validate timezone name (safe constructor)
                try {
                    $tz = new \DateTimeZone($timezone);
                } catch (\Exception $e) {
                    throw new ScraperException("Invalid timezone '{$timezone}'", $roundNumber, ScraperException::GAMESTATUS, $e);
                }
            
                // Now safely parse the date
                $lastTickAt = Carbon::createFromFormat('H:i l jS F Y', "$time $date", $tz);
            
                if (!$lastTickAt || $lastTickAt->format('H:i l jS F Y') !== "$time $date") {
                    throw new ScraperException("Parsed datetime mismatch", $roundNumber, ScraperException::GAMESTATUS);
                }
            } catch (\Exception $e) {
                throw new ScraperException("Failed to parse last tick timestamp: " . $e->getMessage(), $roundNumber, ScraperException::GAMESTATUS, $e);
            }
            
            $fields = [
                'tick_speed' => self::extractSeconds($textBlock, '/Tick Speed:\s*(\d+)\s*(minute|hour)s?/i', $roundNumber, ScraperException::GAMESTATUS),
                'ticking' => self::extractBoolean($textBlock, '/Ticking:\s*(Yes|No)/i', $roundNumber, ScraperException::GAMESTATUS),
                'max_membercount' => self::extractInt($textBlock, '/Max Membercount:\s*(\d+)/i', $roundNumber, ScraperException::GAMESTATUS),
                'members_counting_towards_alliance_score' => self::extractInt($textBlock, '/Members Counting Towards Alliance Score:\s*(\d+)/i', $roundNumber, ScraperException::GAMESTATUS),
                'xp_per_tick_defending_universe' => self::extractInt($textBlock, '/XP\/Tick Defending Universe:\s*(\d+)/i', $roundNumber, ScraperException::GAMESTATUS),
                'xp_per_tick_defending_galaxy' => self::extractInt($textBlock, '/XP\/Tick Defending Galaxy:\s*(\d+)/i', $roundNumber, ScraperException::GAMESTATUS),
                'xp_landing_defense' => self::extractInt($textBlock, '/XP Landing Defense:\s*(\d+)/i', $roundNumber, ScraperException::GAMESTATUS),
                'max_cap' => self::extractInt($textBlock, '/Max Cap:\s*(\d+)%/i', $roundNumber, ScraperException::GAMESTATUS),
                'max_structures_destroyed' => self::extractInt($textBlock, '/Max Structures Destroyed:\s*(\d+)%/i', $roundNumber, ScraperException::GAMESTATUS),
                'salvage_from_attacking_ships' => self::extractInt($textBlock, '/Salvage From Attacking Ships:\s*(\d+)%/i', $roundNumber, ScraperException::GAMESTATUS),
                'salvage_from_defending_ships' => self::extractInt($textBlock, '/Salvage From Defending Ships:\s*(\d+)%/i', $roundNumber, ScraperException::GAMESTATUS),
                'asteroid_armor' => self::extractInt($textBlock, '/Asteroid Armor:\s*(\d+)/i', $roundNumber, ScraperException::GAMESTATUS),
                'construction_armor' => self::extractInt($textBlock, '/Construction Armor:\s*(\d+)/i', $roundNumber, ScraperException::GAMESTATUS),
                'damage_done_on_primary_target' => self::extractInt($textBlock, '/Damage Done On Primary Target:\s*(\d+)%/i', $roundNumber, ScraperException::GAMESTATUS),
                'damage_done_on_secondary_target' => self::extractInt($textBlock, '/Damage Done On Secondary Target:\s*(\d+)%/i', $roundNumber, ScraperException::GAMESTATUS),
                'damage_done_on_tertiary_target' => self::extractInt($textBlock, '/Damage Done On Tertiary Target:\s*(\d+)%/i', $roundNumber, ScraperException::GAMESTATUS),
                'pods_die_when_capping' => self::extractBoolean($textBlock, '/Pods Die When Capping:\s*(Yes|No)/i', $roundNumber, ScraperException::GAMESTATUS),
                'structure_killers_die' => self::extractBoolean($textBlock, '/Structure Killers Die:\s*(Yes|No)/i', $roundNumber, ScraperException::GAMESTATUS),
                'stealship_steal_die_ratio' => self::extractInt($textBlock, '/Stealship Steal\/Die Ratio:\s*(\d+)%/i', $roundNumber, ScraperException::GAMESTATUS),
            ];

            Round::updateOrCreate(
                ['number' => $roundNumber],
                array_merge($fields, [
                    'name' => $roundName,
                    'current_tick' => $currentTick,
                    'last_tick_happened_at' => $lastTickAt,
                ])
            );
        } catch (\Throwable $e) {
            $this->error("Scraping error: " . $e->getMessage());
            throw new ScraperException('Failed to parse game status', $roundNumber ?? 0, ScraperException::GAMESTATUS, $e);
        }
    }
    public static function parseGovernments(int $roundNumber): void
    {
        try {
            $client = new Client();
            $url = Config::get('planetarion.planetarion_manual.governments');
            $response = $client->get($url);

            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            $maintext = $crawler->filter('#contents > div.container > div.maintext');

            $maintext->filter('h2')->each(function (Crawler $headerNode) use ($roundNumber) {
                $name = trim($headerNode->text());
                $paragraphs = $headerNode->nextAll()->filter('p');

                $description = $paragraphs->eq(0)->count()
                    ? strip_tags($paragraphs->eq(0)->html())
                    : null;

                $details = $paragraphs->eq(1)->count()
                    ? strip_tags($paragraphs->eq(1)->html())
                    : null;

                $fields = [
                    'mining_output' => self::extractInt($details, '/Mining output is (\d+)% (higher|lower)./i', $roundNumber, ScraperException::GOVERNMENT),
                    'research' => self::extractInt($details, '/Research is (\d+)% (faster|slower)./i', $roundNumber, ScraperException::GOVERNMENT),
                    'construction' => self::extractInt($details, '/Construction is (\d+)% (faster|slower)./i', $roundNumber, ScraperException::GOVERNMENT),
                    'alert' => self::extractInt($details, '/Alert is (\d+)% (higher|lower)./i', $roundNumber, ScraperException::GOVERNMENT),
                    'stealth' => self::extractInt($details, '/Stealth is (\d+)% (higher|lower)./i', $roundNumber, ScraperException::GOVERNMENT),
                    'production_time' => self::extractInt($details, '/Production time is (\d+)% (faster|slower)./i', $roundNumber, ScraperException::GOVERNMENT),
                    'production_cost' => self::extractInt($details, '/Production cost is (\d+)% (higher|lower)./i', $roundNumber, ScraperException::GOVERNMENT),
                ];

                $gov = Government::firstOrCreate(['name' => $name]);

                RoundGovernmentData::updateOrCreate([
                    'round_number' => $roundNumber,
                    'government_id' => $gov->id,
                    ], array_merge($fields, [
                        'description' => $description,
                    ])
                );
            });
            
        } catch (\Throwable $e) {
            throw new ScraperException('Failed to parse governments', $roundNumber, ScraperException::GOVERNMENT, $e);
        }
    }

    public static function parseRaces(int $roundNumber): void
    {
        try {
            $client = new Client();
            $url = Config::get('planetarion.planetarion_manual.races');
            $response = $client->get($url);
    
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);
    
            $maintext = $crawler->filter('#contents > div.container > div.maintext');
    
            $maintext->filter('h2')->each(function (Crawler $headerNode) use ($roundNumber) {
                $race_header = trim($headerNode->text());
                if (preg_match('/(\w+)\s*\((\w+)\)/', $race_header, $m)) {
                    $name           = $m[1];
                    $abbreviation   = $m[2];
                } else {
                    throw new ScraperException('Failed to match regex on race header (name + abbreviation)', $roundNumber, ScraperException::RACES);
                }
                $paragraphs = $headerNode->nextAll()->filter('p');
    
                $description = $paragraphs->eq(0)->count()
                    ? strip_tags($paragraphs->eq(0)->html())
                    : null;
    
                $details = $paragraphs->eq(2)->count()
                    ? strip_tags($paragraphs->eq(2)->html())
                    : null;
    
                $fields = [
                    'max_stealth' => self::extractInt($details, '/Max stealth: (\d+)/i', $roundNumber, ScraperException::RACES),
                    'stealth_growth_per_tick' => self::extractInt($details, '/Stealth Growth\/Tick: (\d+)/i', $roundNumber, ScraperException::RACES),
                    'base_construction_units' => self::extractInt($details, '/Base Construction Units: (\d+)/i', $roundNumber, ScraperException::RACES),
                    'base_research_points' => self::extractInt($details, '/Base Research Points: (\d+)/i', $roundNumber, ScraperException::RACES),
                    'salvage_bonus' => self::extractInt($details, '/Salvage Bonus: (\d+)/i', $roundNumber, ScraperException::RACES),
                    'production_time_bonus' => self::extractInt($details, '/Production Time bonus: (\d+)%/i', $roundNumber, ScraperException::RACES),
                    'universe_trade_tax' => self::extractInt($details, '/Universe Trade Tax: (\d+)%/i', $roundNumber, ScraperException::RACES),
                ];
    
                $race = Race::firstOrCreate([
                    'name' => $name
                    ], [
                    'abbreviation' => $abbreviation
                    ]
                );
    
                RoundRaceData::updateOrCreate([
                    'round_number' => $roundNumber,
                    'race_id' => $race->id,
                    ], array_merge($fields, [
                        'description' => $description,
                    ])
                );
            });
            
        } catch (\Throwable $e) {
            throw new ScraperException('Failed to parse races', $roundNumber, ScraperException::RACES, $e);
        }
    }

    public static function parseStatsXML(int $roundNumber): void
    {
        try {
            $client = new Client();
            $url = Config::get('planetarion.planetarion_manual.stats_xml');
            $response = $client->get($url);
            $xml = new SimpleXMLElement($response->getBody()->getContents());
    
            foreach ($xml->ships->ship as $shipNode) {
                $name = (string) $shipNode->name;                                   // e.g., Thief
                $raceName = ucfirst(strtolower(trim((string) $shipNode->race)));    // e.g., Zikonian
                $className = ucfirst(strtolower(trim((string) $shipNode->class)));  // e.g., Frigate
                $type = strtoupper(trim((string) $shipNode->type));                 // e.g., Steal
                $etaValue = (int) $shipNode->baseeta;                               // e.g., 13    
    
                $target1Name = ucfirst(strtolower(trim((string) $shipNode->target1)));
                $target2Name = ucfirst(strtolower(trim((string) $shipNode->target2)));
                $target3Name = ucfirst(strtolower(trim((string) $shipNode->target3)));
    
                // Normalize references
                $ship = Ship::firstOrCreate(['name' => $name]);                 // e.g., Harpy, Locust
                $race = Race::firstOrFail(['name' => $raceName]);               // e.g., Terran, Cathaar - Note: ParseRaces must be called first
                $unitClass = UnitClass::firstOrCreate(['name' => $className]);  // e.g., Fighter, Frigate
                $weaponType = WeaponType::firstOrCreate(['name' => $type]);     // e.g., Normal, EMP
                $eta = Eta::firstOrCreate(['value' => $etaValue]);              // e.g., 12, 13
    
                $target1 = UnitClass::firstOrCreate(['name' => $target1Name]);
                $target2 = $target2Name !== '' ? UnitClass::firstOrCreate(['name' => $target2Name]) : null;
                $target3 = $target3Name !== '' ? UnitClass::firstOrCreate(['name' => $target3Name]) : null;
    
                // Store round-specific data
                RoundShipData::updateOrCreate([
                    'round_number' => $roundNumber,
                    'ship_id' => $ship->id,
                ], [
                    'races_id' => $race->id,
                    'unit_class_id' => $unitClass->id,
                    'weapon_type_id' => $weaponType->id,
                    'eta_id' => $eta->id,
                    'target1_id' => $target1->id,
                    'target2_id' => $target2?->id,
                    'target3_id' => $target3?->id,
                    'cloaked' => (bool) $shipNode->cloaked,
                    'initiative' => (int) $shipNode->initiative,
                    'guns' => (int) $shipNode->guns,
                    'armor' => (int) $shipNode->armor,
                    'damage' => (int) $shipNode->damage,
                    'empres' => (int) $shipNode->empres,
                    'metal' => (int) $shipNode->metal,
                    'crystal' => (int) $shipNode->crystal,
                    'eonium' => (int) $shipNode->eonium,
                    'armorcost' => (int) $shipNode->armorcost,
                    'damagecost' => (int) $shipNode->damagecost,
                ]);
            }
    
        } catch (\Throwable $e) {
            throw new ScraperException('Failed to parse statsxml', $roundNumber, ScraperException::STATSXML, $e);
        }
    }

    public static function parsePlanets(int $roundNumber): void
    {
        $client = new Client();
        $url = Config::get('planetarion.botfiles.planet_listing');
        $response = $client->get($url);

        $html = $response->getBody()->getContents();
    }

    public static function parseGalaxies(int $roundNumber): void
    {
        $client = new Client();
        $url = Config::get('planetarion.botfiles.galaxy_listing');
        $response = $client->get($url);

        $html = $response->getBody()->getContents();      
    }

    public static function parseAlliances(int $roundNumber): void
    {
        $client = new Client();
        $url = Config::get('planetarion.botfiles.alliance_listing');
        $response = $client->get($url);

        $html = $response->getBody()->getContents();
    }

    public static function parseUserfeed(int $roundNumber): void
    {
        $client = new Client();
        $url = Config::get('planetarion.botfiles.user_feed');
        $response = $client->get($url);

        $html = $response->getBody()->getContents();
    }

    private static function extractInt(string $text, string $regex, int $roundNumber = 0, int $errorCode = ScraperException::GENERIC): int
    {
        if (preg_match($regex, $text, $m)) {
            $value = (int) $m[1];
            if (isset($m[2])) {
                $modifier = strtolower($m[2]);
                return in_array($modifier, ['higher', 'faster']) ? $value : -$value;
            }
            return $value;
        }
    
        throw new ScraperException("Failed to extract Int from text: {$text}", $roundNumber, $errorCode);
    }

    private static function extractSeconds(string $text, string $regex, int $roundNumber = 0, int $errorCode = ScraperException::GENERIC): int
    {
        if (preg_match($regex, $text, $m)) {
            $value = (int) $m[1];
            $unit = strtolower($m[2]);
            return $unit === 'hour' ? $value * 3600 : $value * 60;
        }
        throw new ScraperException("Failed to extract Seconds from text: {$text}", $roundNumber, $errorCode);
    }

    private static function extractBoolean(string $text, string $regex, int $roundNumber = 0, int $errorCode = ScraperException::GENERIC): bool
    {
        if (preg_match($regex, $text, $m)) {
            $value = strtolower($m[1]);

            if ($value === 'yes') {
                return true;
            }
            return false;
        }
        throw new ScraperException("Failed to extract Boolean from text: {$text}", $roundNumber, $errorCode);
    }
}

