<?php

declare(strict_types=1);

namespace App\Enum;

enum GameStatus: string
{
    case WAITING = 'waiting';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
}
