<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Factory\UserFactory;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\GameService;
use App\Service\UpdatePlayerStats;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(GameService::class)]
class GameServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private GameService $gameService;
    private GameRepository $gameRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = $this->getContainer();

        if ($this->name() === 'testFinishGameMarksAsFinishedAndSetsWinner') {
            $mockStats = $this->createMock(UpdatePlayerStats::class);
            $mockStats->expects($this->once())->method('updateStats');
            $container->set(UpdatePlayerStats::class, $mockStats);
        }

        $this->gameService = $container->get(GameService::class);
        $this->gameRepository = $container->get(GameRepository::class);

        $userFactory = $container->get(UserFactory::class);
        $userRepository = $container->get(UserRepository::class);
        $this->player1 = $userFactory->create('player1@example.com', 'password');
        $userRepository->save($this->player1, true);
        $this->player2 = $userFactory->create('player2@example.com', 'password');
        $userRepository->save($this->player2, true);
    }

    public function testCreateNewGamePersistsItCorrectly()
    {
        $game = $this->gameService->createNewGame($this->player1);

        $gameDb = $this->gameRepository->find($game->getId());

        $this->assertInstanceOf(Game::class, $gameDb);
        $this->assertNotNull($gameDb->getId());
        $this->assertSame($this->player1, $gameDb->getPlayer1());
        $this->assertNull($gameDb->getPlayer2());
        $this->assertInstanceOf(\DateTimeImmutable::class, $gameDb->getCreatedAt());
        $this->assertSame(GameStatus::WAITING_FOR_ANOTHER_PLAYER, $gameDb->getStatus());
        $this->assertNull($gameDb->getCurrentTurn());
        $this->assertNull($gameDb->getWinner());
        $this->assertNull($gameDb->getFinishedAt());
        $this->assertCount(0, $gameDb->getPlayersReady());
    }

    public function testJoinGameSetsPlayer2AndCurrentTurn()
    {
        $newGame = $this->gameService->createNewGame($this->player1);
        $game = $this->gameService->joinGame($newGame, $this->player2);

        $gameDb = $this->gameRepository->find($game->getId());

        $this->assertInstanceOf(Game::class, $gameDb);
        $this->assertSame($this->player2, $gameDb->getPlayer2());
        $this->assertSame(GameStatus::PLACING_SHIPS, $gameDb->getStatus());
        $this->assertInstanceOf(User::class, $gameDb->getCurrentTurn());
        $this->assertContains($gameDb->getCurrentTurn(), [$this->player1, $this->player2]);
    }

    public function testFinishGameMarksAsFinishedAndSetsWinner()
    {
        $game = $this->gameService->createNewGame($this->player1);
        $this->gameService->joinGame($game, $this->player2);
        $game = $this->gameService->finishGame($game, $this->player1);

        $gameDb = $this->gameRepository->find($game->getId());

        $this->assertInstanceOf(Game::class, $gameDb);
        $this->assertSame(GameStatus::GAME_FINISHED, $gameDb->getStatus());
        $this->assertInstanceOf(User::class, $gameDb->getWinner());
        $this->assertInstanceOf(\DateTimeImmutable::class, $gameDb->getFinishedAt());
        $this->assertContains($gameDb->getWinner(), [$this->player1, $this->player2]);
    }
}
