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
    )
    {
    }

    public function getBoardForPlayer(Game $game, User $player, bool $viewOwnBoard): array
    {
        $board = [];

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
                    $yourBoard = $this->boardRepository->findOneBy([
                        'game' => $game,
                        'player' => $player,
                    ]);

                    // If viewing own board
                    $ship = $yourBoard->findShipAtPosition($x, $y);
                    $cell['ship'] = $ship ? $ship->getType()->value : null;
                    $shot = $yourBoard->findShotAt($x, $y);
                } else {
                    // If viewing enemy board
                    $enemy = $game->getOpponent($player);
                    $enemyBoard = $this->boardRepository->findOneBy([
                        'game' => $game,
                        'player' => $enemy,
                    ]);

                    $ship = $enemyBoard->findShipAtPosition($x, $y);
                    $shot = $enemyBoard->findShotAt($x, $y);

                    if ($ship && $shot) {
                        $cell['ship'] = $ship->getType()->value;
                    }
                }

                if ($shot) {
                    if ($shot->getResult() === ShotResult::HIT) {
                        $cell['hit'] = true;
                    } else {
                        $cell['miss'] = true;
                    }

                    if ($shot->getResult() === ShotResult::SUNK) {
                        $cell['sunk'] = true;
                    }
                }

                $board[$y][$x] = $cell;
            }
        }

        return $board;
    }

}
