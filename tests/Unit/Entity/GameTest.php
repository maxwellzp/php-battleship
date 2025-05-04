<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Game::class)]
class GameTest extends TestCase
{
    public function testConstructorAndGettersWorkingCorrectly(): void
    {
        $player1 = $this->createMock(User::class);
        $game = new Game($player1);

        $this->assertSame($player1, $game->getPlayer1());
        $this->assertCount(0, $game->getPlayersReady());
        $this->assertNull($game->getPlayer2());
        $this->assertNull($game->getCurrentTurn());
        $this->assertNull($game->getWinner());
        $this->assertNull($game->getFinishedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $game->getCreatedAt());
        $this->assertSame(GameStatus::WAITING_FOR_ANOTHER_PLAYER, $game->getStatus());
        $this->assertNull($game->getId());
    }
}
