<?php

namespace tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use src\GameStatsBot;
use src\strategy\FortniteStats;

class GameStatsBotTest extends TestCase
{
    #[Test]
    public function should_return_ninja_stats(): void
    {
        $gameStatsBot = new GameStatsBot();
        $gameStatsBot->setGameStatsStrategy(new FortniteStats());
        $this->assertEquals('Stats Fortnite de Ninja', $gameStatsBot->getStats('Ninja'));
    }
}