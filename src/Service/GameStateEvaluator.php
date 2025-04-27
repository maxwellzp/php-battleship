<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\BoardRepository;

class GameStateEvaluator
{
    public function __construct(private readonly BoardRepository $boardRepository)
    {
    }

    public function isGameOver(Game $game): bool
    {
        return $this->hasPlayerLost($game, $game->getPlayer1()) ||
            $this->hasPlayerLost($game, $game->getPlayer2());
    }

    public function getWinner(Game $game): ?User
    {
        if (!$this->isGameOver($game)) {
            return null;
        }

        if ($this->hasPlayerLost($game, $game->getPlayer1())) {
            return $game->getPlayer1();
        }

        if ($this->hasPlayerLost($game, $game->getPlayer2())) {
            return $game->getPlayer1();
        }

        return null; // Draw or error case
    }

    private function hasPlayerLost(Game $game, User $player): bool
    {
        $board = $this->boardRepository->findOneBy([
            'game' => $game,
            'player' => $player,
        ]);

        foreach ($board->getShips() as $ship) {
            if (!$ship->isSunk()) {
                return false;
            }
        }

        return true;
    }
}