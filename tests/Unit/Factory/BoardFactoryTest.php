<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Board;
use App\Entity\Game;
use App\Entity\User;
use App\Factory\BoardFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardFactory::class)]
class BoardFactoryTest extends TestCase
{
    public function testCreateReturnsCorrectBoard()
    {
        $game = $this->createMock(Game::class);
        $user = $this->createMock(User::class);

        $factory = new BoardFactory();
        $board = $factory->create($game, $user);

        $this->assertInstanceOf(Board::class, $board);
        $this->assertEquals($board->getGame(), $game);
        $this->assertEquals(10, $board->getHeight());
        $this->assertEquals(10, $board->getWidth());
        $this->assertSame($game, $board->getGame());
        $this->assertSame($user, $board->getPlayer());
        $this->assertCount(0, $board->getShips());
        $this->assertCount(0, $board->getShots());
    }

}