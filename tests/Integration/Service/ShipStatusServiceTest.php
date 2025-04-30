<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Board;
use App\Entity\Ship;
use App\Entity\User;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Enum\ShotResult;
use App\Factory\BoardFactory;
use App\Factory\GameFactory;
use App\Factory\ShipFactory;
use App\Factory\ShotFactory;
use App\Factory\UserFactory;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Repository\ShipRepository;
use App\Repository\ShotRepository;
use App\Repository\UserRepository;
use App\Service\ShipStatusService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ShipStatusService::class)]
class ShipStatusServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private EntityManagerInterface $entityManager;
    private Ship $ship;
    private ShotFactory $shotFactory;
    private Board $boardPlayer1;
    private User $player2;
    private BoardRepository $boardRepository;
    private ShotRepository $shotRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);

        $userFactory = $container->get(UserFactory::class);
        $userRepository = $container->get(UserRepository::class);
        $player1 = $userFactory->create('player1@example.com', 'password');
        $userRepository->save($player1, true);
        $this->player2 = $userFactory->create('player2@example.com', 'password');
        $userRepository->save($this->player2, true);

        $gameFactory = $container->get(GameFactory::class);
        $gameRepository = $container->get(GameRepository::class);
        $game = $gameFactory->create($player1);
        $gameRepository->save($game, true);

        $boardFactory = self::getContainer()->get(BoardFactory::class);
        $this->boardRepository = $container->get(BoardRepository::class);
        $this->boardPlayer1 = $boardFactory->create($game, $player1);
        $boardPlayer2 = $boardFactory->create($game, $this->player2);
        $this->boardRepository->save($this->boardPlayer1, true);
        $this->boardRepository->save($boardPlayer2, true);


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
        $this->shotRepository = $container->get(ShotRepository::class);
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
