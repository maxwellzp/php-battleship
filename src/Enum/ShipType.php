<?php

declare(strict_types=1);

namespace App\Enum;

enum ShipType: string
{
    case CARRIER = 'Carrier';
    case BATTLESHIP = 'Battleship';
    case CRUISER = 'Cruiser';
    case SUBMARINE = 'Submarine';
    case DESTROYER = 'Destroyer';

    public function getSize(): int
    {
        return match($this)
        {
            ShipType::CARRIER => 5,
            ShipType::BATTLESHIP => 4,
            ShipType::CRUISER, ShipType::SUBMARINE => 3,
            ShipType::DESTROYER => 2,
        };
    }
}
