<?php

namespace tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use src\GameStatsBot;
use src\strategy\FortniteStats;

class GameStatsBotTest extends TestCase
{
    #[Test]
    public function shoud_return_file(): void
    {
        $player = 'iTau_';
        $forniteStats = new GameStatsBot();
        $forniteStats->setGameStatsStrategy(new FortniteStats());

        $imagePath = $forniteStats->getStats($player);

        $this->assertFileExists($imagePath);
    }

    #[Test]
    public function shoud_return_cant_find_player_message(): void
    {
        $player = 'iTaukix';
        $forniteStats = new GameStatsBot();
        $forniteStats->setGameStatsStrategy(new FortniteStats());

        $message = $forniteStats->getStats($player);

        $this->assertEquals('Joueur introuvable', $message);
    }

    #[Test]
    public function shoud_return_private_player_message(): void
    {
        $player = 'tasoeur';
        $forniteStats = new GameStatsBot();
        $forniteStats->setGameStatsStrategy(new FortniteStats());

        $message = $forniteStats->getStats($player);

        $this->assertEquals('Les stats du joueur sont privées', $message);
    }

    #[Test]
    public function shoud_return_exception_http_message(): void
    {
        $player = null;
        $forniteStats = new GameStatsBot();
        $forniteStats->setGameStatsStrategy(new FortniteStats());

        $this->expectExceptionMessage('Erreur HTTP : 400');
        $forniteStats->getStats($player);
    }
}