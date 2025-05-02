<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\DTO\ShipDTO;
use App\Entity\Ship;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Exception\InvalidPlacementException;
use App\Service\ShipPlacer;
use App\Tests\Helper\GameTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ShipPlacer::class)]
class ShipPlacerTest extends KernelTestCase
{
    use GameTestTrait;
    use ResetDatabase;
    use Factories;

    protected function setUp(): void
    {
        $this->bootGameTestKernel();
        $this->initializeGameWithPlayersAndBoards([]);
    }

    public function testPlaceShipsWithValidCoordinatesPersistShipsIntoDatabase(): void
    {
        $shipsDTO = $this->getShips();
        $expectedCount = count($shipsDTO);
        $this->shipPlacer->placeShips($this->boardPlayer1, $shipsDTO);

        $shipsDb = $this->shipRepository->findAll();
        $this->assertCount($expectedCount, $shipsDb);

        foreach ($shipsDb as $shipDb) {
            $filteredResult = array_filter($shipsDTO, function (ShipDTO $shipDTO) use ($shipDb) {
                return $shipDTO->name === $shipDb->getType()
                    && $shipDTO->orientation === $shipDb->getOrientation()
                    && $shipDTO->coords === $shipDb->getCoordinates();
            });
            $shipDTO = reset($filteredResult);
            $this->assertInstanceOf(ShipDTO::class, $shipDTO);
            $this->assertInstanceOf(Ship::class, $shipDb);
            $this->assertNotNull($shipDb->getId());
            $this->assertSame($shipDb->getBoard(), $this->boardPlayer1);
            $this->assertSame($shipDTO->name, $shipDb->getType());
            $this->assertSame($shipDTO->orientation, $shipDb->getOrientation());
            $this->assertSame($shipDTO->coords, $shipDb->getCoordinates());
        }
    }

    public function testPlaceShipsWithInvalidCoordinatesThrowsException(): void
    {
        $x = 8;
        $y = 9;
        $shipsDTO =  [
            new ShipDTO(ShipType::DESTROYER, ShipOrientation::HORIZONTAL, [
                ["x" => $x, "y" => $y],
            ]),
            new ShipDTO(ShipType::DESTROYER, ShipOrientation::VERTICAL, [
                ["x" => $x, "y" => $y],
            ])
        ];

        $exceptionMsg= sprintf('Ship overlaps at position (%d, %d).', $x, $y);
        $this->expectException(InvalidPlacementException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $this->shipPlacer->placeShips($this->boardPlayer1, $shipsDTO);
    }


    public function testIsShipsValidWithValidShipsDoesNotThrowException()
    {
        $shipsDTO = $this->getShips();

        $this->expectNotToPerformAssertions();

        $this->shipPlacer->isShipsValid($this->boardPlayer1, $shipsDTO);
    }

    public function testIsShipsValidWithInvalidShipsThrowException()
    {
        $shipsDTO =  [
            new ShipDTO(ShipType::DESTROYER, ShipOrientation::HORIZONTAL, [
                ["x" => -5, "y" => 9],
                ["x" => 9, "y" => 9],
            ])
        ];

        $this->expectException(InvalidPlacementException::class);

        $this->shipPlacer->isShipsValid($this->boardPlayer1, $shipsDTO);
    }



    #[DataProvider('invalidCoordinatesProvider')]
    public function testValidatePlacementThrowsExceptionForInvalidCoordinates(array $coords, string $expectedMessage): void
    {
        $this->expectException(InvalidPlacementException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->shipPlacer->validatePlacement($this->boardPlayer1, [$coords]);
    }

    public static function invalidCoordinatesProvider(): array
    {
        return [
            [['x' => -1, 'y' => 5], 'Coordinate (-1, 5) is out of bounds.'],
            [['x' => 5, 'y' => -1], 'Coordinate (5, -1) is out of bounds.'],
            [['x' => 10, 'y' => 5], 'Coordinate (10, 5) is out of bounds.'],
            [['x' => 5, 'y' => 10], 'Coordinate (5, 10) is out of bounds.'],
        ];
    }

    public function testPlaceShipsWithPartiallyOverlappingShipsThrowsException(): void
    {
        $shipsDTO = [
            new ShipDTO(ShipType::SUBMARINE, ShipOrientation::VERTICAL, [
                ['x' => 0, 'y' => 0],
                ['x' => 0, 'y' => 1],
                ['x' => 0, 'y' => 2],
            ]),
            new ShipDTO(ShipType::DESTROYER, ShipOrientation::VERTICAL, [
                ['x' => 0, 'y' => 2],
                ['x' => 0, 'y' => 3],
            ]),
        ];

        $this->expectException(InvalidPlacementException::class);
        $this->expectExceptionMessage('Ship overlaps at position (0, 2).');

        $this->shipPlacer->placeShips($this->boardPlayer1, $shipsDTO);
    }


}

