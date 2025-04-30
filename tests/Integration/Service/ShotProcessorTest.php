<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Board;
use App\Entity\Ship;
use App\Entity\Shot;
use App\Entity\User;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Enum\ShotResult;
use App\Exception\InvalidShotException;
use App\Factory\BoardFactory;
use App\Factory\GameFactory;
use App\Factory\ShipFactory;
use App\Factory\ShotFactory;
use App\Factory\UserFactory;
use App\Helpers\CoordinateConverter;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Repository\ShipRepository;
use App\Repository\ShotRepository;
use App\Repository\UserRepository;
use App\Service\GameEventLogger;
use App\Service\ShotProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ShotProcessor::class)]
class ShotProcessorTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private Ship $ship;
    private ShotFactory $shotFactory;
    private Board $boardPlayer1;
    private User $player2;
    private BoardRepository $boardRepository;
    private ShotRepository $shotRepository;
    private GameEventLogger $gameEventLogger;
    private CoordinateConverter $coordinateConverter;
    private ShipRepository $shipRepository;
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->shotRepository = $container->get(ShotRepository::class);
        $this->gameEventLogger = $container->get(GameEventLogger::class);
        $this->coordinateConverter = $container->get(CoordinateConverter::class);
        $this->shipRepository = $container->get(ShipRepository::class);
        $this->shotFactory = $container->get(ShotFactory::class);

        $userFactory = $container->get(UserFactory::class);
        $userRepository = $container->get(UserRepository::class);
        $this->player1 = $userFactory->create('player1@example.com', 'password');
        $userRepository->save($this->player1, true);
        $this->player2 = $userFactory->create('player2@example.com', 'password');
        $userRepository->save($this->player2, true);


        $gameFactory = $container->get(GameFactory::class);
        $gameRepository = $container->get(GameRepository::class);
        $game = $gameFactory->create($this->player1);
        $gameRepository->save($game, true);

        $boardFactory = self::getContainer()->get(BoardFactory::class);
        $this->boardRepository = $container->get(BoardRepository::class);
        $this->boardPlayer1 = $boardFactory->create($game, $this->player1);
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
    }

    public function testProcessShotWithCorrectDataInsertShotIntoDatabase()
    {
        $shotProcessor = new ShotProcessor(
            $this->shotFactory,
            $this->shotRepository,
            $this->gameEventLogger,
            $this->coordinateConverter,
            $this->shipRepository,
            $this->boardRepository
        );
        $x = 0;
        $y = 0;
        $shot = $shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);
        self::assertInstanceOf(Shot::class, $shot);

        $shotDb = $this->shotRepository->find($shot->getId());

        $this->assertNotNull($shotDb->getId());
        $this->assertEquals($x, $shotDb->getX());
        $this->assertEquals($y, $shotDb->getY());
        $this->assertSame($this->boardPlayer1, $shotDb->getBoard());
        $this->assertSame($this->player2, $shotDb->getPlayer());
        $this->assertInstanceOf(\DateTimeImmutable::class, $shotDb->getFiredAt());
    }

    public function testProcessShotWithShipCoordinatesMarkItAsHit()
    {
        $shotProcessor = new ShotProcessor(
            $this->shotFactory,
            $this->shotRepository,
            $this->gameEventLogger,
            $this->coordinateConverter,
            $this->shipRepository,
            $this->boardRepository
        );
        $x = 0;
        $y = 0;
        $shot = $shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);

        $this->assertSame(ShotResult::HIT, $shot->getResult());
    }

    public function testProcessShotWithWaterCoordinatesMarkItAsMiss()
    {
        $shotProcessor = new ShotProcessor(
            $this->shotFactory,
            $this->shotRepository,
            $this->gameEventLogger,
            $this->coordinateConverter,
            $this->shipRepository,
            $this->boardRepository
        );
        $x = 9;
        $y = 9;
        $shot = $shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);

        $this->assertSame(ShotResult::MISS, $shot->getResult());
    }

    public function testProcessShotWithSameCoordinatesThrowsException()
    {
        $shotProcessor = new ShotProcessor(
            $this->shotFactory,
            $this->shotRepository,
            $this->gameEventLogger,
            $this->coordinateConverter,
            $this->shipRepository,
            $this->boardRepository
        );
        $x = 0;
        $y = 0;
        $shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);

        $this->expectException(InvalidShotException::class);
        $this->expectExceptionMessage('This position has already been targeted.');

        $shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);
    }
}
