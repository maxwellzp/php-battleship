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
        Board           $board,
        ShipType        $type,
        ShipOrientation $shipOrientation,
        array           $coordinates
    ): Ship
    {
        return new Ship($board, $type, $shipOrientation, $coordinates);
    }
}
