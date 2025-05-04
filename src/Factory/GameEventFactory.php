<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Game;
use App\Entity\GameEvent;

class GameEventFactory
{
    public function create(Game $game, string $message, $createdAt = new \DateTimeImmutable()): GameEvent
    {
        return new GameEvent($game, $message, $createdAt);
    }
}
