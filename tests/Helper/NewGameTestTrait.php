<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Entity\Game;
use App\Service\GameService;

trait NewGameTestTrait
{
    use UserTestTrait;
    protected Game $game;
    protected GameService $gameService;

    protected function bootGameTestKernel(): void
    {
        $this->bootUserTestKernel();
        $container = static::getContainer();

        $this->gameService = $container->get(GameService::class);
    }

    public function initializeGameWithPlayers(): void
    {
        $this->createUsers();
        $this->game = $this->gameService->createNewGame($this->player1);
        $this->game = $this->gameService->joinGame($this->game, $this->player2);
    }
}