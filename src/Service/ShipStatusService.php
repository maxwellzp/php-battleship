<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Ship;
use App\Entity\User;
use App\Repository\ShipRepository;

class ShipStatusService
{
    public function __construct(
        private readonly ShipRepository $shipRepository,
        private readonly MercureService $mercureService,
    ) {
    }

    public function updateShipSunkStatus(Ship $ship, User $player): void
    {
        $shots = $ship->getBoard()->getShots();

        if ($ship->isSunkByShots($shots) && !$ship->isSunk()) {
            $ship->setIsSunk(true);
            $this->shipRepository->save($ship, true);

            $this->mercureService->publishShipIsSunk($ship, $player->getId()->toString());
        }
    }
}
