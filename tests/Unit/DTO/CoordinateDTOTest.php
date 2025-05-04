<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\CoordinateDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CoordinateDTO::class)]
class CoordinateDTOTest extends TestCase
{
    public function testDtoPropertiesAreSetCorrectly(): void
    {
        $x = 2;
        $y = 5;
        $coordinate = new CoordinateDTO($x, $y);

        $this->assertEquals($x, $coordinate->x);
        $this->assertEquals($y, $coordinate->y);
    }
}