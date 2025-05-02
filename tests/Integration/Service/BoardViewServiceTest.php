<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\DTO\ShipDTO;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Service\BoardViewService;
use App\Service\ShipPlacer;
use App\Service\ShotProcessor;
use App\Tests\Helper\GameTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(BoardViewService::class)]
class BoardViewServiceTest extends KernelTestCase
{
    use GameTestTrait;
    use ResetDatabase;
    use Factories;

    private BoardViewService $boardViewService;
    private ShotProcessor $shotProcessor;
    protected function setUp(): void
    {
        $this->bootGameTestKernel();
        $this->initializeGameWithPlayersAndBoards([]);

        $container = $this->getContainer();
        $this->boardViewService = $container->get(BoardViewService::class);
        $shipPlacer = $container->get(ShipPlacer::class);
        $this->shotProcessor = $container->get(ShotProcessor::class);

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

    public function testGetBoardForPlayerDisplaysCorrectOwnBoardAfterHits()
    {
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 0, 0);
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 0, 1);
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 0, 2);

        $yourBoard = $this->boardViewService->getBoardForPlayer($this->game, $this->player1, true);

        for ($y = 0; $y < 10; $y++) {
            for ($x = 0; $x < 10; $x++) {
                $cell = $yourBoard[$y][$x];
                $this->assertCellCoordinates($cell, $x, $y);

                if ($x === 0 && $y < 3) {
                    $this->assertShipCell($cell, 'Submarine', true, true);
                } else {
                    $this->assertEmptyCell($cell);
                }
            }
        }
    }

    public function testGetBoardForPlayerDisplaysCorrectOwnBoardAfterMissShots()
    {
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 9, 0);
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 9, 1);
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 9, 2);

        $yourBoard = $this->boardViewService->getBoardForPlayer($this->game, $this->player1, true);

        for ($y = 0; $y < 10; $y++) {
            for ($x = 0; $x < 10; $x++) {
                $cell = $yourBoard[$y][$x];
                $this->assertCellCoordinates($cell, $x, $y);

                if ($x === 0 && $y < 3) {
                    $this->assertShipCell($cell, 'Submarine', false);
                } elseif ($x === 9 && $y < 3) {
                    $this->assertMissedCell($cell);
                } else {
                    $this->assertEmptyCell($cell);
                }
            }
        }
    }

    public function testGetBoardForPlayerDisplaysCorrectOpponentBoardAfterHits()
    {
        $this->shotProcessor->processShot($this->boardPlayer2, $this->player1, 0, 0);
        $this->shotProcessor->processShot($this->boardPlayer2, $this->player1, 0, 1);

        $opponentBoard = $this->boardViewService->getBoardForPlayer($this->game, $this->player1, false);


        for ($y = 0; $y < 10; $y++) {
            for ($x = 0; $x < 10; $x++) {
                $cell = $opponentBoard[$y][$x];
                $this->assertCellCoordinates($cell, $x, $y);

                if ($x === 0 && $y < 3) {
                    $this->assertShipCell($cell, 'Submarine', $y < 2, false);
                } else {
                    $this->assertEmptyCell($cell);
                }
            }
        }
    }

    public function testGetBoardForPlayerDisplaysPartiallyHitShipAsNotSunk()
    {
        // Hit only 2 of the 3 submarine cells
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 0, 0);
        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 0, 1);

        $yourBoard = $this->boardViewService->getBoardForPlayer($this->game, $this->player1, true);

        for ($row = 0; $row < count($yourBoard); $row++) {
            for ($column = 0; $column < count($yourBoard[$row]); $column++) {
                $cell = $yourBoard[$row][$column];
                $this->assertCellCoordinates($cell, $column, $row);
                if ($column === 0 && $row < 3) {
                    $this->assertEquals('Submarine', $cell['ship']);
                    $this->assertFalse($cell['miss']);
                    $this->assertFalse($cell['sunk']);
                    if ($row === 0 || $row === 1) {
                        $this->assertTrue($cell['hit']);
                    } else {
                        $this->assertFalse($cell['hit']);
                    }
                } else {
                    $this->assertEmptyCell($cell);
                }
            }
        }
    }

    private function assertCellCoordinates(array $cell, int $expectedX, int $expectedY): void
    {
        $this->assertEquals($expectedX, $cell['x']);
        $this->assertEquals($expectedY, $cell['y']);
    }

    private function assertShipCell(
        array $cell,
        string $expectedType,
        bool $hit,
        bool $sunk = false,
        bool $miss = false
    ): void {
        $this->assertEquals($expectedType, $cell['ship']);
        $this->assertSame($hit, $cell['hit']);
        $this->assertSame($sunk, $cell['sunk']);
        $this->assertSame($miss, $cell['miss']);
    }

    private function assertEmptyCell(array $cell): void
    {
        $this->assertNull($cell['ship']);
        $this->assertFalse($cell['hit']);
        $this->assertFalse($cell['sunk']);
        $this->assertFalse($cell['miss']);
    }

    private function assertMissedCell(array $cell): void
    {
        $this->assertNull($cell['ship']);
        $this->assertFalse($cell['hit']);
        $this->assertFalse($cell['sunk']);
        $this->assertTrue($cell['miss']);
    }
}
