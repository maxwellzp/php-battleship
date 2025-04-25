<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Factory\GameLogFactory;
use App\Repository\GameLogRepository;

class GameLogService
{
    public function __construct(
        private GameLogFactory $gameLogFactory,
        private GameLogRepository $gameLogRepository,
    )
    {

    }

    public function log(Game $game, string $message): void
    {
        $gameLog = $this->gameLogFactory->create($game, $message);
        $this->gameLogRepository->save($gameLog);
    }
}
