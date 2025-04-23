<?php

return [
    'status_main_game' => env('PLANETARION_STATUS_MAIN_GAME', 'https://www.planetarion.com/games/status/game'),

    'planetarion_manual' => [
        'governments' => env('PLANETARION_MANUAL_GOVERNMENTS', 'https://game.planetarion.com/manual.pl?page=governments'),
        'races' => env('PLANETARION_MANUAL_RACES', 'https://game.planetarion.com/manual.pl?page=races'),
        'stats_xml' => env('PLANETARION_MANUAL_STATS_XML', 'https://game.planetarion.com/manual.pl?action=statsxml'),
    ],
    'botfiles' => [
        'planet_listing' => env('PLANETARION_BOTFILES_PLANET_LISTING', 'https://game.planetarion.com/botfiles/planet_listing.txt'),
        'galaxy_listing' => env('PLANETARION_BOTFILES_GALAXY_LISTING', 'https://game.planetarion.com/botfiles/galaxy_listing.txt'),
        'alliance_listing' => env('PLANETARION_BOTFILES_ALLIANCE_LISTING', 'https://game.planetarion.com/botfiles/alliance_listing.txt'),
        'user_feed' => env('PLANETARION_BOTFILES_USER_FEED', 'https://game.planetarion.com/botfiles/user_feed.txt'),
    ],
];