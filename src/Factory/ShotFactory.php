<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Board;
use App\Entity\Shot;
use App\Entity\User;
use App\Enum\ShotResult;

class ShotFactory
{
    public function create(
        Board      $board,
        User       $player,
        int        $x,
        int        $y,
        ShotResult $shotResult,
                   $firedAt = new \DateTimeImmutable()
    ): Shot
    {
        return new Shot($board, $player, $x, $y, $shotResult, $firedAt);
    }
}
