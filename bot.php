<?php

use Discord\Discord;
use Discord\Exceptions\IntentException;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/key.php';

$key = getKey();

try {
    $discord = new Discord([
        'token' => $key,
    ]);
} catch (IntentException $e) {
    echo $e->getMessage();
    exit(1);
}

$discord->run();
