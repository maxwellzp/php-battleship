<?php

declare(strict_types=1);

namespace App\Helpers;

class CoordinateConverter
{
    private const LETTERS = ['A','B','C','D','E','F','G','H','I','J'];

    public function toHumanReadable(int $x, int $y): string
    {
        if ($x < 0 || $x > 10) {
            throw new \Exception('X must be between 0 and 9');
        }
        $letter = self::LETTERS[$x];
        $number = $y + 1;
        return $letter . $number;
    }
}
