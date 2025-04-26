<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Game;
use App\Entity\GameEvent;

class GameEventFactory
{
    public function create(Game $game, string $message): GameEvent
    {
        $GameEvent = new GameEvent();
        $GameEvent->setGame($game);
        $GameEvent->setMessage($message);
        $GameEvent->setCreatedAt(new \DateTimeImmutable());

        return $GameEvent;
    }
}
