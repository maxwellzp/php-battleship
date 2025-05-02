<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Board;
use App\Entity\Shot;
use App\Entity\User;
use App\Enum\ShotResult;
use App\Factory\ShotFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShotFactory::class)]
class ShotFactoryTest extends TestCase
{
    public function testCreateReturnsCorrectShot()
    {
        $board = $this->createMock(Board::class);
        $user = $this->createMock(User::class);
        $x = 0;
        $y = 4;

        $factory = new ShotFactory();
        $shot = $factory->create($board, $user, $x, $y, ShotResult::HIT);

        $this->assertInstanceOf(Shot::class, $shot);
        $this->assertSame($board, $shot->getBoard());
        $this->assertSame($user, $shot->getPlayer());
        $this->assertSame(ShotResult::HIT, $shot->getResult());
        $this->assertNotNull($shot->getFiredAt());
        $this->assertEquals($x, $shot->getX());
        $this->assertEquals($y, $shot->getY());
    }
}
