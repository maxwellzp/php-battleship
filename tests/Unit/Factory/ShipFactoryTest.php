<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Board;
use App\Entity\Ship;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Factory\ShipFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShipFactory::class)]
class ShipFactoryTest extends TestCase
{
    public function testCreateReturnsCorrectShip()
    {
        $board = $this->createMock(Board::class);
        $shipType = ShipType::DESTROYER;
        $coords = [
            ['x' => 1, 'y' => 2],
            ['x' => 2, 'y' => 2],
        ];

        $factory = new ShipFactory();
        $ship = $factory->create($board, $shipType, ShipOrientation::VERTICAL, $coords);

        $this->assertInstanceOf(Ship::class, $ship);
        $this->assertSame($shipType, $ship->getType());
        $this->assertSame(ShipOrientation::VERTICAL, $ship->getOrientation());
        $this->assertSame($board, $ship->getBoard());
        $this->assertSame($coords, $ship->getCoordinates());
        $this->assertFalse($ship->isSunk());
        $this->assertEquals($shipType->getSize(), $ship->getSize());
    }
}
