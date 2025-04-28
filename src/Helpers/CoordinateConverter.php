<?php

declare(strict_types=1);

namespace App\Helpers;

class CoordinateConverter
{
    private const LETTERS = ['A','B','C','D','E','F','G','H','I','J'];

    public function toHumanReadable(int $x, int $y): string
    {
        $letter = self::LETTERS[$x] ?? '?';
        $number = $y + 1;
        return $letter . $number;
    }
}
