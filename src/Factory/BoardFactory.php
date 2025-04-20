<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Board;

class BoardFactory
{
    public function create(int $width = 10, int $height = 10): Board
    {
        $board = new Board();
        $board->setWidth($width);
        $board->setHeight($height);
        return $board;
    }
}