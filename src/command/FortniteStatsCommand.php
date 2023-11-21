<?php

namespace src\command;

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Exception;
use src\GameStatsBot;
use src\strategy\FortniteStats;

class FortniteStatsCommand implements Command
{
    /**
     * @throws Exception
     */
    public function __construct(Discord $discord)
    {
        $this->creation($discord);
    }

    /**
     * Create the command
     * @param Discord $discord
     * @return void
     * @throws Exception
     */
    public function creation(Discord $discord): void
    {
        $command = new CommandBuilder();
        $command->setName(FORTNITE_STATS_COMMAND_NAME);
        $command->setDescription('Affiche les stats Fortnite d\'un joueur');
        $command->addOption(new Option($discord, [
            'name' => 'player',
            'description' => 'Le joueur dont vous voulez voir les stats',
            'type' => Option::STRING,
            'required' => true,
        ]));

        $discord->application->commands->save(
            $discord->application->commands->create($command->toArray())
        );

        echo 'Commande FortniteStats créée', PHP_EOL;
    }

    /**
     * Create a listener for the command
     * @param Discord $discord
     * @return void
     */
    public static function listen(Discord $discord): void
    {
        $discord->listenCommand(FORTNITE_STATS_COMMAND_NAME, function (Interaction $interaction) {
            $player = $interaction->data->options['player']->value;

            $forniteStats = new GameStatsBot();
            $forniteStats->setGameStatsStrategy(new FortniteStats());

            $imagePath = $forniteStats->getStats($player);

            if (file_exists($imagePath)) {
                $interaction->respondWithMessage(
                    (new MessageBuilder())
                        ->addFile($imagePath, 'FortniteStats.png'));
            } else {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($imagePath));
            }
        });
    }
}
