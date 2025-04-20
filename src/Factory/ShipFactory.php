<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Board;
use App\Entity\Ship;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;

class ShipFactory
{
    public function create(
        Board $board,
        ShipType $type,
        ShipOrientation $shipOrientation,
        array $coordinates
    ): Ship
    {
        $ship = new Ship($type);
        $ship->setBoard($board);
        $ship->setCoordinates($coordinates);
        $ship->setOrientation($shipOrientation);
        $ship->setIsSunk(false);
        return $ship;
    }
}