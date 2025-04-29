<?php

declare(strict_types=1);

namespace App\Tests\Unit\Helpers;

use App\Helpers\CoordinateConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CoordinateConverter::class)]
class CoordinateConverterTest extends TestCase
{

    public function testToHumanReadableWithCorrectArgumentsReturnsCorrectPosition()
    {
        $x = 0;
        $y = 0;
        $converter = new CoordinateConverter();
        $result = $converter->toHumanReadable($x, $y);
        $this->assertEquals("A1", $result);
    }

    public function testToHumanReadableWithIncorrectArgumentThrowsException()
    {
        $x = 99;
        $y = 0;
        $converter = new CoordinateConverter();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('X must be between 0 and 9');

        $converter->toHumanReadable($x, $y);
    }
}