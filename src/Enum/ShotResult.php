<?php

declare(strict_types=1);

namespace App\Enum;

enum ShotResult: string
{
    case HIT = 'hit';
    case MISS = 'miss';
    case SUNK = 'sunk';
}