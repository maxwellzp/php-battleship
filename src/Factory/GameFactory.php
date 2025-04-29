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
        $game->setPlayer1($user);
        $game->setStatus(GameStatus::WAITING_FOR_ANOTHER_PLAYER);
        $game->setCreatedAt(new \DateTimeImmutable());
        return $game;
    }
}
