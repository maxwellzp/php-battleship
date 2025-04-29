<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\Shot;
use App\Entity\User;
use App\Enum\ShotResult;
use App\Exception\InvalidShotException;
use App\Factory\ShotFactory;
use App\Helpers\CoordinateConverter;
use App\Repository\ShipRepository;
use App\Repository\ShotRepository;

class ShotProcessor
{
    public function __construct(
        private readonly ShotFactory         $shotFactory,
        private readonly ShotRepository      $shotRepository,
        private readonly GameEventLogger     $gameEventLogger,
        private readonly CoordinateConverter $coordinateConverter,
        private readonly ShipRepository      $shipRepository,
    )
    {
    }

    public function processShot(Board $board, User $user, int $x, int $y): Shot
    {
        // Check if shot already exists
        if ($this->hasAlreadyBeenShot($board, $x, $y)) {
            throw new InvalidShotException('This position has already been targeted.');
        }

        $shot = $this->shotFactory->create($board, $user, $x, $y, ShotResult::MISS);

        $ship = $this->findShipAtPosition($board, $x, $y);

        if ($ship) {
            $shot->setResult(ShotResult::HIT);
            $this->markShipHit($ship, $x, $y);
        }

        $this->shotRepository->save($shot, true);

        $this->log($board->getGame(), $user, $x, $y, $shot);

        return $shot;
    }

    private function hasAlreadyBeenShot(Board $board, int $x, int $y): bool
    {
        return $this->shotRepository->findOneBy([
                'board' => $board,
                'x' => $x,
                'y' => $y,
            ]) !== null;
    }

    private function findShipAtPosition(Board $board, int $x, int $y): ?Ship
    {
        foreach ($board->getShips() as $ship) {
            foreach ($ship->getCoordinates() as $position) {
                if ($position['x'] === $x && $position['y'] === $y) {
                    return $ship;
                }
            }
        }

        return null;
    }

    private function markShipHit(Ship $ship, int $x, int $y): void
    {
        $positions = $ship->getCoordinates();
        foreach ($positions as &$position) {
            if ($position['x'] === $x && $position['y'] === $y) {
                $position['hit'] = true;
            }
        }
        $ship->setCoordinates($positions);
        $this->shipRepository->save($ship);
    }

    private function isShipSunk(Ship $ship): bool
    {
        foreach ($ship->getCoordinates() as $position) {
            if (empty($position['hit'])) {
                return false;
            }
        }
        return true;
    }

    private function log(Game $game, User $user, int $x, int $y, Shot $shot): void
    {
        $this->gameEventLogger->log(
            $game,
            sprintf("Player: %s attacked %s: %s",
                $user->getUsername(),
                $this->coordinateConverter->toHumanReadable($x, $y),
                $shot->getResult()->value
            ));
    }
}