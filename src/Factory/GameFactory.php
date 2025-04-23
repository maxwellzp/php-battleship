<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;

class GameFactory
{
    public function create(User $user): Game
    {
        $game = new Game();
        $game->setCurrentTurn($user);
        $game->setCreatedAt(new \DateTimeImmutable());
        $game->setStatus(GameStatus::WAITING);
        return $game;
    }
}