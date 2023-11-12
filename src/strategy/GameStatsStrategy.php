<?php

namespace src\strategy;

interface GameStatsStrategy
{
    public function getStats(string $player): string;
}