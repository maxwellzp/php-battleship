<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Game;
use App\Entity\User;

class GameFactory
{
    public function create(User $player1): Game
    {
        return new Game($player1);
    }
}
