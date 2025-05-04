<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Board;
use App\Entity\Ship;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Ship::class)]
class ShipTest extends TestCase
{
    public function testConstructorAndGettersWorkingCorrectly(): void
    {
        $board = $this->createMock(Board::class);
        $shipType = ShipType::DESTROYER;
        $orientation = ShipOrientation::VERTICAL;
        $coords = [
            ['x' => 1, 'y' => 2],
            ['x' => 2, 'y' => 2],
        ];

        $ship = new Ship($board, $shipType, $orientation, $coords);

        $this->assertSame($shipType, $ship->getType());
        $this->assertSame($orientation, $ship->getOrientation());
        $this->assertSame($board, $ship->getBoard());
        $this->assertSame($coords, $ship->getCoordinates());
        $this->assertFalse($ship->isSunk());
        $this->assertEquals($shipType->getSize(), $ship->getSize());
    }
}