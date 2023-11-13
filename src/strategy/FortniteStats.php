<?php

namespace src\strategy;

use GdImage;
use RuntimeException;

class FortniteStats implements GameStatsStrategy
{
    private const API = '3f251c80-74d9-418a-94c6-fb809c155976';
    private const FORTNITE_STATS_BASE_IMAGE_PATH = 'assets/images/FortniteStatsEmpty.png';
    private const FORTNITE_STATS_OUTPUT_IMAGE_PATH = 'assets/images/FortniteStats.png';
    private const FONT_PATH = 'assets/fonts/Aganè 75 Extra Bold.ttf';
    private const PLAYER_NAME_SIZE = 35;
    private const PLAYER_STATS_SIZE = 25;
    private const PLAYER_NAME_X = 225;
    private const PLAYER_NAME_Y = 175;
    private const PLAYER_STAT_OVERALL_FIRST_X = 85;
    private const PLAYER_STAT_OVERALL_FIRST_Y = 275;
    private const PLAYER_STAT_OVERALL_SECOND_X = 105;
    private const PLAYER_STAT_OVERALL_SECOND_Y = 400;
    private const PLAYER_STAT_SOLO_X = 535;
    private const PLAYER_STAT_SOLO_Y = 160;
    private const PLAYER_STAT_DUO_X = 535;
    private const PLAYER_STAT_DUO_Y = 390;
    private const PLAYER_STAT_SQUAD_X = 535;
    private const PLAYER_STAT_SQUAD_Y = 620;

    public function getStats(string $player): string
    {
        $url = "https://fortnite-api.com/v2/stats/br/v2?name=" . urlencode($player);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . self::API,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("Erreur cURL : $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        switch ($httpCode) {
            case 400:
                return "Le nom du joueur est invalide";
            case 403:
                return "Les stats du joueur sont privées";
            case 404:
                return "Joueur introuvable";
            case 200:
                break;
            default:
                throw new RuntimeException("Erreur HTTP : $httpCode");
        }

        $data = json_decode($response, true);

        if ($data === null) {
            throw new RuntimeException("Erreur de décodage JSON");
        }

        return $this->selectData($data);
    }

    public function selectData(array $data): string
    {
        $playerName = $data['data']['account']['name'];
        $stats = $data['data']['stats']['all'];
        $types = ['overall', 'solo', 'duo', 'squad'];

        $embed = [
            'title' => 'Stats Fortnite de ' . $playerName,
            'description' => 'Voici les stats Fortnite de ' . $playerName,
            'color' => 0x00ff00,
            'fields' => [],
        ];

        foreach ($types as $type) {
            $typeStats = $stats[$type];
            $isOverall = ($type == 'overall');

            $embed['fields'] = $this->addStatsField($embed['fields'], $type, $typeStats, $isOverall);
        }

        return $this->createStatsImage($playerName, $embed);
    }

    private function addStatsField(mixed $fields, string $type, mixed $typeStats, bool $isOverall): array
    {
        $fieldNames = [
            'Matches' => $typeStats['matches'] ?? '0',
            'Wins' => $typeStats['wins'] ?? '0',
            'Wins %' => isset($typeStats['matches']) ? round($typeStats['winRate'], 1) : '0',
            'Kills' => $typeStats['kills'] ?? '0',
            'Deaths' => $typeStats['deaths'] ?? '0',
            'K/D' => isset($typeStats['matches']) ? round($typeStats['kd'], 1) : '0',
            'KPM' => isset($typeStats['matches']) ? round($typeStats['killsPerMatch'], 1) : '0',
        ];

        if ($isOverall) {
            $fieldNamesOverall = [
                'Wins' => $typeStats['wins'] ?? '0',
                'Wins %' => isset($typeStats['matches']) ? round($typeStats['winRate'], 1) : '0',
                'K/D' => isset($typeStats['matches']) ? round($typeStats['kd'], 1) : '0',
                'KPM' => isset($typeStats['matches']) ? round($typeStats['killsPerMatch'], 1) : '0',
                'Matches' => $typeStats['matches'] ?? '0',
                'Kills' => $typeStats['kills'] ?? '0',
                'Deaths' => $typeStats['deaths'] ?? '0',
            ];

            $fieldNames = array_merge($fieldNamesOverall, $fieldNames);
        }

        $prefix = ucfirst($type) . ' ';

        foreach ($fieldNames as $name => $value) {
            $fields[] = [
                'name' => $prefix . $name,
                'value' => $value,
            ];
        }

        return $fields;
    }

    public function createStatsImage(string $playerName, array $stats): string
    {
        $image = imagecreatefrompng(self::FORTNITE_STATS_BASE_IMAGE_PATH);
        $textColor = imagecolorallocate($image, 255, 255, 255);

        $this->writePlayerName($image, $textColor, $playerName);
        $this->writePlayerStats($image, $textColor, $stats);

        $outputImagePath = self::FORTNITE_STATS_OUTPUT_IMAGE_PATH;

        imagepng($image, $outputImagePath);
        imagedestroy($image);

        return $outputImagePath;
    }

    private function writePlayerName(GdImage $image, int $textColor, string $playerName): void
    {
        imagettftext($image, self::PLAYER_NAME_SIZE, 0, $this->centeredText(self::PLAYER_NAME_X, self::PLAYER_NAME_SIZE, $playerName), self::PLAYER_NAME_Y, $textColor, self::FONT_PATH, $playerName);
    }

    private function writePlayerStats(GdImage|false $image, false|int $textColor, array $stats): void
    {
        $fields = $stats['fields'];
        $firstLane = true;
        $counterOverall = 0;
        $counterOther = 0;

        foreach ($fields as $field) {
            $value = $field['value'] ? : '0';

            if ($counterOther === 7) {
                $counterOther = 0;
            }

            $multiply = match ($counterOther) {
                1 => 120,
                3, 4 => 115,
                5 => 113,
                default => 111,
            };

            switch (explode(' ', $field['name'])[0]) {
                case 'Overall':
                    if ($counterOverall === 4) {
                        $firstLane = false;
                        $counterOverall = 0;
                    }

                    if ($firstLane) {
                        $x = self::PLAYER_STAT_OVERALL_FIRST_X + ($counterOverall * 95);
                        $y = self::PLAYER_STAT_OVERALL_FIRST_Y;
                    } else {
                        $x = self::PLAYER_STAT_OVERALL_SECOND_X + ($counterOverall * 122);
                        $y = self::PLAYER_STAT_OVERALL_SECOND_Y;
                    }

                    imagettftext($image, self::PLAYER_STATS_SIZE, 0, $this->centeredText($x, self::PLAYER_STATS_SIZE, $value), $y, $textColor, self::FONT_PATH, $value);
                    $counterOverall++;
                    break;

                case 'Solo':
                    imagettftext($image, self::PLAYER_STATS_SIZE, 0, $this->centeredText(self::PLAYER_STAT_SOLO_X + ($counterOther * $multiply) , self::PLAYER_STATS_SIZE, $value), self::PLAYER_STAT_SOLO_Y, $textColor, self::FONT_PATH, $value);
                    $counterOther++;
                    break;

                case 'Duo':
                    imagettftext($image, self::PLAYER_STATS_SIZE, 0, $this->centeredText(self::PLAYER_STAT_DUO_X + ($counterOther * $multiply) , self::PLAYER_STATS_SIZE, $value), self::PLAYER_STAT_DUO_Y, $textColor, self::FONT_PATH, $value);
                    $counterOther++;
                    break;

                case 'Squad':
                    imagettftext($image, self::PLAYER_STATS_SIZE, 0, $this->centeredText(self::PLAYER_STAT_SQUAD_X + ($counterOther * $multiply) , self::PLAYER_STATS_SIZE, $value), self::PLAYER_STAT_SQUAD_Y, $textColor, self::FONT_PATH, $value);
                    $counterOther++;
                    break;
            }
        }
    }

    private function centeredText(int $x, int $size, string $text): int|float
    {
        $textBox = imagettfbbox($size, 0, self::FONT_PATH, $text);
        $textWidth = $textBox[2] - $textBox[0];

        return round($x - ($textWidth / 2));
    }
}