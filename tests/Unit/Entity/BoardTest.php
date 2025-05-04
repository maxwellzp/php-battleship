<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Board;
use App\Entity\Game;
use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Board::class)]
class BoardTest extends TestCase
{
    public function testConstructorAndGettersWorkingCorrectly(): void
    {
        $game = $this->createMock(Game::class);
        $player = $this->createMock(User::class);
        $width = 10;
        $height = 10;

        $board = new Board($game, $player, $width, $height);

        $this->assertSame($game, $board->getGame());
        $this->assertSame($player, $board->getPlayer());
        $this->assertequals($width, $board->getWidth());
        $this->assertequals($height, $board->getHeight());
        $this->assertCount(0, $board->getShips());
        $this->assertCount(0, $board->getShots());
        $this->assertNull($board->getId());
    }
}