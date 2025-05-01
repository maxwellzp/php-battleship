<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Service\GameService;
use App\Service\UpdatePlayerStats;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(UpdatePlayerStats::class)]
class UpdatePlayerStatsTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private Game $game;
    private UpdatePlayerStats $updatePlayerStats;
    private User $player1;
    private User $player2;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootKernel();

        $container = $this->getContainer();

        $gameService = $container->get(GameService::class);
        $this->userRepository = $container->get(UserRepository::class);

        $userFactory = $container->get(UserFactory::class);
        $this->player1 = $userFactory->create('player1@example.com', 'password');
        $this->userRepository->save($this->player1, true);
        $this->player2 = $userFactory->create('player2@example.com', 'password');
        $this->userRepository->save($this->player2, true);

        $this->updatePlayerStats = $container->get(UpdatePlayerStats::class);

        $this->game = $gameService->createNewGame($this->player1);
        $gameService->joinGame($this->game, $this->player2);
    }

    public function testUpdateStatsWhenPlayer1Wins()
    {
        $this->game->setWinner($this->player1);

        $this->updatePlayerStats->updateStats($this->game);

        $updatedPlayer1 = $this->userRepository->find($this->player1->getId());
        $updatedPlayer2 = $this->userRepository->find($this->player2->getId());

        $this->assertEquals(1, $updatedPlayer1->getWins());
        $this->assertEquals(0, $updatedPlayer2->getWins());
        $this->assertEquals(0, $updatedPlayer1->getLosses());
        $this->assertEquals(1, $updatedPlayer2->getLosses());
    }

    public function testUpdateStatsWhenPlayer2Wins()
    {
        $this->game->setWinner($this->player2);

        $this->updatePlayerStats->updateStats($this->game);

        $updatedPlayer1 = $this->userRepository->find($this->player1->getId());
        $updatedPlayer2 = $this->userRepository->find($this->player2->getId());

        $this->assertEquals(1, $updatedPlayer2->getWins());
        $this->assertEquals(0, $updatedPlayer2->getLosses());
        $this->assertEquals(0, $updatedPlayer1->getWins());
        $this->assertEquals(1, $updatedPlayer1->getLosses());
    }

    public function testUpdateStatsWithWinnerNullThrowsException()
    {
        $this->game->setWinner(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('After the game is finished, the winner should be set');

        $this->updatePlayerStats->updateStats($this->game);
    }
}
