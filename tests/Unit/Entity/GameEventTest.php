<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Game;
use App\Entity\GameEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GameEvent::class)]
class GameEventTest extends TestCase
{
    public function testConstructorAndGettersWorkingCorrectly(): void
    {
        $game = $this->createMock(Game::class);
        $expectedMsg = "player-player2@example.com attacked A1: hit";
        $createdAt = new \DateTimeImmutable('2025-04-27 10:00:00');

        $event = new GameEvent($game, $expectedMsg, $createdAt);

        $this->assertSame($game, $event->getGame());
        $this->assertSame($expectedMsg, $event->getMessage());
        $this->assertSame($createdAt, $event->getCreatedAt());
        $this->assertNull($event->getId());
    }
}
