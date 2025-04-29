<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Factory\GameFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GameFactory::class)]
class GameFactoryTest extends TestCase
{
    public function testCreateReturnsCorrectGame()
    {
        $player1 = $this->createMock(User::class);

        $factory = new GameFactory();
        $game = $factory->create($player1);

        $this->assertInstanceOf(Game::class, $game);
        $this->assertSame($player1, $game->getPlayer1());
        $this->assertCount(0, $game->getPlayersReady());
        $this->assertNull($game->getPlayer2());
        $this->assertNull($game->getCurrentTurn());
        $this->assertNull($game->getWinner());
        $this->assertNull($game->getFinishedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $game->getCreatedAt());
        $this->assertSame(GameStatus::WAITING_FOR_ANOTHER_PLAYER, $game->getStatus());
    }
}
