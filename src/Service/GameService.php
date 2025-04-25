<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use App\Entity\Shot;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    public function __construct(
        private readonly MercureService         $mercureService,
        private readonly EntityManagerInterface $entityManager,
    )
    {

    }

    public function checkWinner(Board $board): bool
    {
        $isSunkAll = true;
        foreach ($board->getShips() as $ship) {
            $coords = $ship->getCoordinates();
            $hits = $this->entityManager->getRepository(Shot::class)->findBy([
                'board' => $board,
                'hit' => true
            ]);

            $hitCoords = array_map(fn($s) => ['x' => $s->getX(), 'y' => $s->getY()], $hits);

            foreach ($coords as $coord) {
                if (!in_array($coord, $hitCoords)) {
                    $isSunkAll = false;
                    break 2;
                }
            }
        }
        return $isSunkAll;
    }
}