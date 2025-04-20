<?php

declare(strict_types=1);

namespace App\Enum;

enum ShipOrientation: string
{
    case HORIZONTAL = 'horizontal';
    case VERTICAL = 'vertical';
}
