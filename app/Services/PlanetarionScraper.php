<?php

namespace App\Services;

use App\Exceptions\ScraperException;
use App\Models\Eta;
use App\Models\Race;
use App\Models\RoundShipData;
use App\Models\Ship;
use App\Models\UnitClass;
use App\Models\WeaponType;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Symfony\Component\DomCrawler\Crawler;

class PlanetarionScraper
{
    public static function parseGovernments(int $roundNumber): void
    {
        try {
            $client = new Client();
            $url = Config::get('planetarion_manual.governments');
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
                    'mining_output' => self::extractInt($details, '/Mining output is (\d+)% (higher|lower)./i'),
                    'research' => self::extractInt($details, '/Research is (\d+)% (faster|slower)./i'),
                    'construction' => self::extractInt($details, '/Construction is (\d+)% (faster|slower)./i'),
                    'alert' => self::extractInt($details, '/Alert is (\d+)% (higher|lower)./i'),
                    'stealth' => self::extractInt($details, '/Stealth is (\d+)% (higher|lower)./i'),
                    'production_time' => self::extractInt($details, '/Production time is (\d+)% (faster|slower)./i'),
                    'production_cost' => self::extractInt($details, '/Production cost is (\d+)% (higher|lower)./i'),
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
            $url = Config::get('planetarion_manual.races');
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
                    'max_stealth' => self::extractInt($details, '/Max stealth: (\d+)/i'),
                    'stealth_growth_per_tick' => self::extractInt($details, '/Stealth Growth\/Tick: (\d+)/i'),
                    'base_construction_units' => self::extractInt($details, '/Base Construction Units: (\d+)/i'),
                    'base_research_points' => self::extractInt($details, '/Base Research Points: (\d+)/i'),
                    'salvage_bonus' => self::extractInt($details, '/Salvage Bonus: (\d+)/i'),
                    'production_time_bonus' => self::extractInt($details, '/Production Time bonus: (\d+)%/i'),
                    'universe_trade_tax' => self::extractInt($details, '/Universe Trade Tax: (\d+)%/i'),
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
            $url = Config::get('planetarion_manual.stats_xml');
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
            throw new ScraperException(
                'Failed to parse statsxml',
                $roundNumber,
                ScraperException::STATSXML,
                $e
            );
        }
    }

    private static function extractInt(string $text, string $regex): int
    {
        if (preg_match($regex, $text, $m)) {
            $value = (int) $m[1];
            if (isset($m[2])) {
                $modifier = strtolower($m[2]);
                return in_array($modifier, ['higher', 'faster']) ? $value : -$value;
            }
            return $value;
        }
    
        return 0;
    }
}

