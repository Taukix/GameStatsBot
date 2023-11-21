<?php

use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\WebSockets\Intents;
use src\command\FortniteStatsCommand;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/key.php';

$key = getKey();
const FORTNITE_STATS_COMMAND_NAME = 'fortnite-stats';

try {
    $discord = new Discord([
        'token' => $key,
        'intents' => [
            Intents::GUILDS
        ],
    ]);
} catch (IntentException $e) {
    echo $e->getMessage();
    exit(1);
}

$discord->on('init', function ($discord) {
    echo 'Bot is initializing...', PHP_EOL;
    new FortniteStatsCommand($discord);
});

$discord->on('ready', function ($discord) {
    echo "Bot is ready!", PHP_EOL;

    FortniteStatsCommand::listen($discord);
});

$discord->run();
