<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use Symfony\Component\Validator\Constraints as Assert;

class ShipDTO
{
    /**
     * @param ShipType $name
     * @param ShipOrientation $orientation
     * @param array $coords
     */
    public function __construct(
        public ShipType $name,
        public ShipOrientation $orientation,
        public array $coords,
    ) {
    }
}
