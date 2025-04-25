<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Game;
use App\Entity\GameLog;

class GameLogFactory
{
    public function create(Game $game, string $message): GameLog
    {
        $gameLog = new GameLog();
        $gameLog->setGame($game);
        $gameLog->setMessage($message);
        $gameLog->setCreatedAt(new \DateTimeImmutable());

        return $gameLog;
    }
}
