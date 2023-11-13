<?php

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Exceptions\IntentException;
use src\GameStatsBot;
use src\strategy\FortniteStats;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/key.php';

$key = getKey();

const GENERAL_CHANNEL_NAME = 'fortnite';
const COMMAND_PREFIX = '/';

try {
    $discord = new Discord([
        'token' => $key,
    ]);
} catch (IntentException $e) {
    echo $e->getMessage();
    exit(1);
}

$discord->on('ready', function ($discord) {
    echo "Bot is ready!", PHP_EOL;

    $discord->on('message', function ($message, $discord) {
        if ($message->channel->name !== GENERAL_CHANNEL_NAME) {
            return;
        }

        if (str_starts_with($message->content, COMMAND_PREFIX)) {
            $args = explode(' ', substr($message->content, strlen(COMMAND_PREFIX)));
            $command = array_shift($args);

            if (empty($command) || empty($args)) {
                $message->channel->sendMessage('Il manque des arguments !');
            }

            switch ($command) {
                case 'fortnite-stats':
                    $player = implode(' ', $args);
                    $forniteStats = new GameStatsBot();
                    $forniteStats->setGameStatsStrategy(new FortniteStats());

                    $imagePath = $forniteStats->getStats($player);
                    break;

                default:
                    return;
            }

            if (file_exists($imagePath)) {
                $message->channel->sendMessage(
                    (new MessageBuilder())
                        ->addFile($imagePath, 'FortniteStats.png')
                );
            } else {
                $message->channel->sendMessage('Erreur lors de la génération des statistiques.');
            }
        }
    });
});

$discord->run();
