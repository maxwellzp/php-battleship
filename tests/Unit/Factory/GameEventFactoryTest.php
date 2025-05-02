<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Factory\GameEventFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GameEventFactory::class)]
class GameEventFactoryTest extends TestCase
{
    public function testCreateReturnsCorrectGameEvent()
    {
        $game = $this->createMock(Game::class);
        $expectedMsg = "player-player2@example.com attacked A1: hit";

        $factory = new GameEventFactory();
        $gameEvent = $factory->create($game, $expectedMsg);

        $this->assertInstanceOf(GameEvent::class, $gameEvent);
        $this->assertEquals($expectedMsg, $gameEvent->getMessage());
        $this->assertSame($game, $gameEvent->getGame());
        $this->assertNotNull($gameEvent->getCreatedAt());
    }
}
