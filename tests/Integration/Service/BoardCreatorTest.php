<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Board;
use App\Service\BoardCreator;
use App\Tests\Helper\NewGameTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(BoardCreator::class)]
class BoardCreatorTest extends KernelTestCase
{
    use NewGameTestTrait;
    use ResetDatabase;
    use Factories;

    private BoardCreator $boardCreator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootGameTestKernel();
        $this->initializeGameWithPlayers();

        $this->boardCreator = $this->getContainer()->get(BoardCreator::class);
    }

    public function testCreateBoard(): void
    {
        $boards = $this->boardCreator->createBoards($this->game);

        $this->assertIsArray($boards);
        $this->assertCount(2, $boards);
        [$board1, $board2] = $boards;

        $this->assertInstanceOf(Board::class, $board1);
        $this->assertInstanceOf(Board::class, $board2);
        $this->assertNotNull($board1->getId());
        $this->assertNotNull($board2->getId());
        $this->assertSame($this->game, $board2->getGame());
        $this->assertSame($this->player1, $board1->getPlayer());
        $this->assertSame($this->player2, $board2->getPlayer());
        $this->assertCount(0, $board1->getShips());
        $this->assertCount(0, $board1->getShots());
        $this->assertCount(0, $board2->getShips());
        $this->assertCount(0, $board2->getShots());
    }

    public function test2()
    {
        $this->game->setPlayer1(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game requires both player1 and player2. First player is missing.');

        $this->boardCreator->createBoards($this->game);
    }

    public function test3()
    {
        $this->game->setPlayer2(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game requires both player1 and player2. Second player is missing.');

        $this->boardCreator->createBoards($this->game);
    }
}
