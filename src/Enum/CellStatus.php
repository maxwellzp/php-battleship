<?php

namespace App\Enum;

enum CellStatus: string
{
    case HIT = 'hit';
    case MISS = 'miss';
    case SUNK = 'sunk';
}
