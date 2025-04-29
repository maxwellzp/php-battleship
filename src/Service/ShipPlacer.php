<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ShipDTO;
use App\Entity\Board;
use App\Exception\InvalidPlacementException;
use App\Factory\ShipFactory;
use Doctrine\ORM\EntityManagerInterface;

class ShipPlacer
{
    public function __construct(
        private readonly ShipFactory $shipFactory,
        private readonly EntityManagerInterface $entityManager,
    )
    {

    }

    /**
     * @param Board $board
     * @param ShipDTO[] $ships
     * @return void
     * @throws InvalidPlacementException
     */
    public function isShipsValid(Board $board, array $ships): void
    {
        foreach ($ships as $ship) {
            $this->validatePlacement($board, $ship->coords);
        }
    }

    /**
     * @param Board $board
     * @param ShipDTO[] $ships
     * @return void
     */
    public function placeShips(Board $board, array $ships): void
    {
        foreach ($ships as $ship) {
            $ship = $this->shipFactory->create($board, $ship->name, $ship->orientation, $ship->coords);;

            $this->validatePlacement($board, $ship->getCoordinates());

            $board->addShip($ship);
            $this->entityManager->persist($ship);
        }
        $this->entityManager->persist($board);
        $this->entityManager->flush();
    }

    /**
     * @param Board $board
     * @param array $coordinates
     * @return void
     * @throws InvalidPlacementException
     */
    public function validatePlacement(Board $board, array $coordinates): void
    {
        foreach ($coordinates as $coordinate) {

            $x = $coordinate['x'];
            $y = $coordinate['y'];

            if ($x < 0 || $x > 9 || $y < 0 || $y > 9) {
                throw new InvalidPlacementException(sprintf('Coordinate (%d, %d) is out of bounds.', $x, $y));
            }

            if ($board->hasShipAt($x, $y)) {
                throw new InvalidPlacementException(sprintf('Ship overlaps at position (%d, %d).', $x, $y));
            }
        }
    }
}