<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Factory\GameFactory;
use App\Repository\GameRepository;
use App\Repository\UserRepository;

class GameService
{
    public function __construct(
        private readonly GameFactory $gameFactory,
        private readonly GameRepository $gameRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function createNewGame(User $user): Game
    {
        $game = $this->gameFactory->create($user);
        $this->gameRepository->save($game, true);
        return $game;
    }

    public function joinGame(Game $game, User $user): Game
    {
        $game->setPlayer2($user);
        $game->setStatus(GameStatus::PLACING_SHIPS);

        $firstPlayer = random_int(0, 1) ? $game->getPlayer1() : $game->getPlayer2();
        $game->setCurrentTurn($firstPlayer);

        $this->gameRepository->save($game, true);
        return $game;
    }

    public function finishGame(Game $game, User $winner): Game
    {
        $game->setWinner($winner);
        $game->setStatus(GameStatus::GAME_FINISHED);
        $game->setFinishedAt(new \DateTimeImmutable());
        $this->gameRepository->save($game);

        $winner->setWins($winner->getWins() + 1);
        $this->userRepository->save($winner);

        $opponent = $game->getPlayer1() === $winner ? $game->getPlayer2() : $game->getPlayer1();
        $opponent->setLosses($opponent->getLosses() + 1);
        $this->userRepository->save($opponent);
        return $game;
    }
}
