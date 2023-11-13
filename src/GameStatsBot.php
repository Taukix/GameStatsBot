<?php

namespace src;

use src\strategy\GameStatsStrategy;

class GameStatsBot
{
    private GameStatsStrategy $gameStatsStrategy;

    public function setGameStatsStrategy(GameStatsStrategy $strategy): void
    {
        $this->gameStatsStrategy = $strategy;
    }

    public function getStats(mixed $player): string {
        return $this->gameStatsStrategy->getStats($player);
    }
}