<?php

namespace src\command;

use Discord\Discord;

interface Command {
    public function creation(Discord $discord): void;

    public static function listen(Discord $discord): void;
}