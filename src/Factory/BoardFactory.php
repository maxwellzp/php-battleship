<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Board;
use App\Entity\Game;

class BoardFactory
{
    public function create(Game $game, int $width = 10, int $height = 10): Board
    {
        $board = new Board();
        $board->setGame($game);
        $board->setWidth($width);
        $board->setHeight($height);
        return $board;
    }
}