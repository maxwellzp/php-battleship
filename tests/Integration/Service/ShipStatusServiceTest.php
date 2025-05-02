<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Ship;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Enum\ShotResult;
use App\Factory\ShipFactory;
use App\Factory\ShotFactory;
use App\Repository\ShipRepository;
use App\Service\ShipStatusService;
use App\Tests\Helper\GameTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ShipStatusService::class)]
class ShipStatusServiceTest extends KernelTestCase
{
    use GameTestTrait;
    use ResetDatabase;
    use Factories;

    private EntityManagerInterface $entityManager;
    private Ship $ship;
    private ShotFactory $shotFactory;

    protected function setUp(): void
    {
        $this->bootGameTestKernel();
        $this->initializeGameWithPlayersAndBoards([]);

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);

        $shipFactory = self::getContainer()->get(ShipFactory::class);
        $shipRepository = $container->get(ShipRepository::class);
        $coordinates = [
            ["x" => 0, "y" => 0],
            ["x" => 0, "y" => 1],
            ["x" => 0, "y" => 2]
        ];
        $this->ship = $shipFactory->create(
            $this->boardPlayer1,
            ShipType::SUBMARINE,
            ShipOrientation::VERTICAL,
            $coordinates
        );
        $shipRepository->save($this->ship, true);
        $this->boardPlayer1->addShip($this->ship);
        $this->boardRepository->save($this->boardPlayer1, true);

        $this->shotFactory = self::getContainer()->get(ShotFactory::class);
    }

    public function testUpdateShipSunkStatusWithAllCoordinatesHitMakeShipAsSunk()
    {
        $this->createShots($this->ship->getType()->getSize());
        $shipStatusService = new ShipStatusService($this->entityManager);
        $shipStatusService->updateShipSunkStatus($this->ship);
        $this->assertTrue($this->ship->isSunk());
    }

    public function testUpdateShipSunkStatusWithOnlyOneHitDoestMakeShipAsSunk()
    {
        $this->createShots(1);
        $shipStatusService = new ShipStatusService($this->entityManager);
        $shipStatusService->updateShipSunkStatus($this->ship);
        $this->assertFalse($this->ship->isSunk());
    }

    private function createShots(int $size)
    {
        for ($y = 0; $y < $size; $y++) {
            $shot = $this->shotFactory->create($this->boardPlayer1, $this->player2, 0, $y, ShotResult::HIT);
            $this->shotRepository->save($shot, true);
            $this->boardPlayer1->addShot($shot);
        }
        $this->boardRepository->save($this->boardPlayer1, true);
    }
}
