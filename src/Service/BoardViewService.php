<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\ShotResult;
use App\Repository\BoardRepository;

class BoardViewService
{
    public function __construct(
        private BoardRepository $boardRepository
    ) {
    }

    public function getBoardForPlayer(Game $game, User $player, bool $viewOwnBoard): array
    {
        $board = [];

        $viewerBoard = $this->boardRepository->findOneBy([
            'game' => $game,
            'player' => $player,
        ]);
        $opponent = $game->getOpponent($player);
        $opponentBoard = $this->boardRepository->findOneBy([
            'game' => $game,
            'player' => $opponent,
        ]);
        if (!$viewerBoard || !$opponentBoard) {
            throw new \RuntimeException('Board not found for one or both players.');
        }

        $viewerShots = $viewerBoard->getShots();
        $enemyShots = $opponentBoard->getShots();

        for ($y = 0; $y < 10; $y++) {
            for ($x = 0; $x < 10; $x++) {
                $cell = [
                    'x' => $x,
                    'y' => $y,
                    'ship' => null,
                    'hit' => false,
                    'miss' => false,
                    'sunk' => false,
                ];

                if ($viewOwnBoard) {
                    $ship = $viewerBoard->findShipAtPosition($x, $y);
                    $shot = $viewerBoard->findShotAt($x, $y);
                    $shots = $viewerShots;
                } else {
                    $ship = $opponentBoard->findShipAtPosition($x, $y);
                    $shot = $opponentBoard->findShotAt($x, $y);
                    $shots = $enemyShots;
                }

                if ($ship) {
                    $cell['ship'] = $ship->getType()->value;

                    // Check if the ship is sunk based on shots
                    if ($ship->isSunkByShots($shots)) {
                        $cell['sunk'] = true;
                    }
                }


                if ($shot) {
                    if ($shot->getResult() === ShotResult::HIT) {
                        $cell['hit'] = true;
                    } else {
                        $cell['miss'] = true;
                    }
                }

                $board[$y][$x] = $cell;
            }
        }

        return $board;
    }
}
