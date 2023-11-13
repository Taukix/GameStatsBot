<?php

namespace src\strategy;

interface GameStatsStrategy
{
    public function getStats(mixed $player): string;
    public function selectData(array $data): string;
    public function createStatsImage(string $playerName, array $stats): string;
}