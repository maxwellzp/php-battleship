<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\ShotResult;
use App\Repository\BoardRepository;
use Psr\Log\LoggerInterface;

class BoardViewService
{
    public function __construct(
        private BoardRepository $boardRepository,
        private LoggerInterface $logger
    )
    {
    }

    public function getBoardForPlayer(Game $game, User $player, bool $viewOwnBoard): array
    {
        $board = [];

        $viewerBoard = $this->boardRepository->findOneBy([
            'game' => $game,
            'player' => $player,
        ]);
        $viewerShots = $viewerBoard->getShots();

        $enemy = $game->getOpponent($player);
        $enemyBoard = $this->boardRepository->findOneBy([
            'game' => $game,
            'player' => $enemy,
        ]);
        $enemyShots = $enemyBoard->getShots();

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
                    $ship = $enemyBoard->findShipAtPosition($x, $y);
                    $shot = $enemyBoard->findShotAt($x, $y);
                    $shots = $enemyShots;
                }

                if ($ship) {
                    $cell['ship'] = $ship ? $ship->getType()->value : null;

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
