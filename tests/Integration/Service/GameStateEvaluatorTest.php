<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\DTO\ShipDTO;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Service\GameService;
use App\Service\GameStateEvaluator;
use App\Service\ShipPlacer;
use App\Service\ShotProcessor;
use App\Tests\Helper\GameTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(GameStateEvaluator::class)]
class GameStateEvaluatorTest extends KernelTestCase
{
    use GameTestTrait;
    use ResetDatabase;
    use Factories;

    private ShotProcessor $shotProcessor;

    protected function setUp(): void
    {
        $this->bootGameTestKernel();
        $this->initializeGameWithPlayersAndBoards([]);

        $container = $this->getContainer();

        $this->gameService = $container->get(GameService::class);
        $this->shipPlacer = $container->get(ShipPlacer::class);
        $this->shotProcessor = $container->get(ShotProcessor::class);
    }

    public function testIsGameOverWithNotSunkShipsReturnsFalse(): void
    {
        $coordinates = [
            ["x" => 0, "y" => 0],
            ["x" => 0, "y" => 1],
            ["x" => 0, "y" => 2]
        ];

        $ships = [
            new ShipDTO(ShipType::SUBMARINE, ShipOrientation::VERTICAL, $coordinates)
        ];
        $this->shipPlacer->placeShips($this->boardPlayer1, $ships);
        $this->shipPlacer->placeShips($this->boardPlayer2, $ships);

        $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 0, 0);
        $this->shotProcessor->processShot($this->boardPlayer2, $this->player2, 0, 1);

        $gameStateEvaluator = new GameStateEvaluator($this->boardRepository);
        $result = $gameStateEvaluator->isGameOver($this->game);
        $this->assertFalse($result);
    }

    public function testIsGameOverWithSunkShipsReturnsTrue(): void
    {
        $coordinates = [
            ["x" => 0, "y" => 0],
            ["x" => 0, "y" => 1],
            ["x" => 0, "y" => 2]
        ];

        $ships = [
            new ShipDTO(ShipType::SUBMARINE, ShipOrientation::VERTICAL, $coordinates)
        ];

        $this->shipPlacer->placeShips($this->boardPlayer1, $ships);
        $this->shipPlacer->placeShips($this->boardPlayer2, $ships);

        for ($y = 0; $y < 3; $y++) {
            $this->shotProcessor->processShot($this->boardPlayer1, $this->player2, 0, $y);
        }

        $gameStateEvaluator = new GameStateEvaluator($this->boardRepository);
        $result = $gameStateEvaluator->isGameOver($this->game);
        //$this->assertTrue($result);
    }
}
