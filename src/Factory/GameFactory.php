<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Game;
use App\Enum\GameStatus;

class GameFactory
{
    public function create(): Game
    {
        $game = new Game();
        $game->setCreatedAt(new \DateTimeImmutable());
        $game->setStatus(GameStatus::WAITING);
        return $game;
    }
}