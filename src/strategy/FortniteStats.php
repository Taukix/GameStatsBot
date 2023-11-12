<?php

namespace src\strategy;

class FortniteStats implements GameStatsStrategy
{

    public function getStats(string $player): string
    {
        return 'Stats Fortnite de ' . $player;
    }
}