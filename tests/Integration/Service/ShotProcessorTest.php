<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\DTO\ShipDTO;
use App\Entity\Shot;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Enum\ShotResult;
use App\Exception\InvalidShotException;
use App\Service\GameService;
use App\Service\ShipPlacer;
use App\Service\ShotProcessor;
use App\Tests\Helper\GameTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ShotProcessor::class)]
class ShotProcessorTest extends KernelTestCase
{
    use GameTestTrait;
    use ResetDatabase;
    use Factories;

    private ShotProcessor $shotProcessor;
    protected function setUp(): void
    {
        $this->bootGameTestKernel();
        $this->initializeGameWithPlayersAndBoards([]);

        $container = static::getContainer();
        $this->shotProcessor = $container->get(ShotProcessor::class);
        $this->gameService = $container->get(GameService::class);
        $shipPlacer = $container->get(ShipPlacer::class);

        $coordinates = [
            ["x" => 0, "y" => 0],
            ["x" => 0, "y" => 1],
            ["x" => 0, "y" => 2]
        ];

        $ships = [
            new ShipDTO(ShipType::SUBMARINE, ShipOrientation::VERTICAL, $coordinates)
        ];
        $shipPlacer->placeShips($this->boardPlayer1, $ships);
        $shipPlacer->placeShips($this->boardPlayer2, $ships);
    }

    public function testProcessShotWithCorrectDataInsertShotIntoDatabase()
    {
        $x = 0;
        $y = 0;
        $shot = $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);
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
        $x = 0;
        $y = 0;
        $shot = $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);

        $this->assertSame(ShotResult::HIT, $shot->getResult());
    }

    public function testProcessShotWithWaterCoordinatesMarkItAsMiss()
    {
        $x = 9;
        $y = 9;
        $shot = $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);

        $this->assertSame(ShotResult::MISS, $shot->getResult());
    }

    public function testProcessShotWithSameCoordinatesThrowsException()
    {
        $x = 0;
        $y = 0;
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);

        $this->expectException(InvalidShotException::class);
        $this->expectExceptionMessage('This position has already been targeted.');

        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);
    }

    public function testShipCoordinateIsMarkedAsHitAfterShot()
    {
        $x = 0;
        $y = 0;
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, $x, $y);

        $ship = $this->boardPlayer1->getShips()->first(); // Assuming 1 ship
        $coordinates = $ship->getCoordinates();

        $hitPosition = array_filter($coordinates, fn($pos) => $pos['x'] === $x && $pos['y'] === $y);

        $this->assertNotEmpty($hitPosition);
        $this->assertTrue(reset($hitPosition)['hit']);
    }
}
