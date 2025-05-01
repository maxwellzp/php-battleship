<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Repository\UserRepository;

class UpdatePlayerStats
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function updateStats(Game $game): void
    {
        if (!$game->getWinner()) {
            throw new \Exception('After the game is finished, the winner should be set');
        }

        $winner = $game->getWinner();
        $winner->setWins($winner->getWins() + 1);
        $this->userRepository->save($winner);

        $opponent = $game->getPlayer1() === $winner ? $game->getPlayer2() : $game->getPlayer1();
        $opponent->setLosses($opponent->getLosses() + 1);
        $this->userRepository->save($opponent);
    }
}
