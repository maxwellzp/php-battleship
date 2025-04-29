<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CoordinateDTO
{
    /**
     * @param int $x
     * @param int $y
     */
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }
}
