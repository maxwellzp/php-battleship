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
        Board $board,
        User $user,
        int $x,
        int $y,
        ShotResult $shotResult
    ): Shot {
        $shot = new Shot();
        $shot->setBoard($board);
        $shot->setPlayer($user);
        $shot->setX($x);
        $shot->setY($y);
        $shot->setResult($shotResult);
        $shot->setFiredAt(new \DateTimeImmutable());
        return $shot;
    }
}
