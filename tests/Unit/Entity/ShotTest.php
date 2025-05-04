<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Board;
use App\Entity\Shot;
use App\Entity\User;
use App\Enum\ShotResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Shot::class)]
class ShotTest extends TestCase
{
    public function testConstructorAndGettersWorkingCorrectly(): void
    {
        $board = $this->createMock(Board::class);
        $player = $this->createMock(User::class);
        $x = 1;
        $y = 2;
        $result = ShotResult::HIT;
        $firedAt = new \DateTimeImmutable('2025-04-27 10:00:00');

        $shot = new Shot($board, $player, $x, $y, $result, $firedAt);

        $this->assertSame($board, $shot->getBoard());
        $this->assertSame($player, $shot->getPlayer());
        $this->assertEquals($x, $shot->getX());
        $this->assertEquals($y, $shot->getY());
        $this->assertSame($firedAt, $shot->getFiredAt());
        $this->assertSame($result, $shot->getResult());
        $this->assertNull($shot->getId());
    }

    public function testConstructorDefaultsFiredAt(): void
    {
        $board = $this->createMock(Board::class);
        $player = $this->createMock(User::class);
        $shot = new Shot($board, $player, 0, 0, ShotResult::MISS);

        $this->assertInstanceOf(\DateTimeImmutable::class, $shot->getFiredAt());
    }
}
