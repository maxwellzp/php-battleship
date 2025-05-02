<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Service\UpdatePlayerStats;
use App\Tests\Helper\GameTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(UpdatePlayerStats::class)]
class UpdatePlayerStatsTest extends KernelTestCase
{
    use GameTestTrait;
    use ResetDatabase;
    use Factories;
    private UpdatePlayerStats $updatePlayerStats;

    protected function setUp(): void
    {
        $this->bootGameTestKernel();
        $this->initializeGameWithPlayersAndBoards([]);

        $container = $this->getContainer();

        $this->updatePlayerStats = $container->get(UpdatePlayerStats::class);
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
