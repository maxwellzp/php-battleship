<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ShipDTO;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShipDTO::class)]
class ShipDTOTest extends TestCase
{
    public function testDtoPropertiesAreSetCorrectly(): void
    {
        $coords = [
            ['x' => 1, 'y' => 2],
            ['x' => 2, 'y' => 2],
        ];

        $dto = new ShipDTO(ShipType::DESTROYER, ShipOrientation::HORIZONTAL, $coords);

        $this->assertSame(ShipType::DESTROYER, $dto->name);
        $this->assertSame(ShipOrientation::HORIZONTAL, $dto->orientation);
        $this->assertSame($coords, $dto->coords);
    }
}