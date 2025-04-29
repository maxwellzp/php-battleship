<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Factory\GameEventFactory;
use App\Repository\GameEventRepository;

class GameEventLogger
{
    public function __construct(
        private readonly GameEventFactory $gameEventFactory,
        private readonly GameEventRepository $gameEventRepository,
        private readonly MercureService $mercureService,
    ) {
    }

    public function log(Game $game, string $message): GameEvent
    {
        $event = $this->gameEventFactory->create($game, $message);
        $this->gameEventRepository->save($event, true);

        $this->mercureService->publishGameEvent($event);
        return $event;
    }
}
