<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use App\Entity\Game;
use App\Factory\BoardFactory;
use App\Repository\BoardRepository;

class BoardCreator
{
    public function __construct(
        private BoardRepository $boardRepository,
        private BoardFactory $boardFactory,
    ) {
    }

    /**
     * @param Game $game
     * @return Board[] array
     * @throws \Exception
     */
    public function createBoards(Game $game): array
    {
        if ($game->getPlayer1() === null) {
            throw new \Exception('Game requires both player1 and player2. First player is missing.');
        }

        if ($game->getPlayer2() === null) {
            throw new \Exception('Game requires both player1 and player2. Second player is missing.');
        }

        $board1 = $this->boardFactory->create($game, $game->getPlayer1());
        $this->boardRepository->save($board1, true);

        $board2 = $this->boardFactory->create($game, $game->getPlayer2());
        $this->boardRepository->save($board2, true);

        return [$board1, $board2];
    }
}
