<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Ship;
use Doctrine\ORM\EntityManagerInterface;

class ShipStatusService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function updateShipSunkStatus(Ship $ship): void
    {
        $shots = $ship->getBoard()->getShots();

        if ($ship->isSunkByShots($shots) && !$ship->isSunk()) {
            $ship->setIsSunk(true);
            $this->entityManager->flush();
        }
    }
}
