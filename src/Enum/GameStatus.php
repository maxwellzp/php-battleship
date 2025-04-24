<?php

declare(strict_types=1);

namespace App\Enum;

enum GameStatus: string
{
    case WAITING_FOR_ANOTHER_PLAYER = 'waiting_for_another_player';
    case PLACING_SHIPS = 'placing_ships';
    case IN_PROGRESS = 'in_progress';
    case GAME_FINISHED = 'game_finished';
}
