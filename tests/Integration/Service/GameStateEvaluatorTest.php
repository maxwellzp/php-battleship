<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\DTO\ShipDTO;
use App\Entity\Board;
use App\Entity\User;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Factory\BoardFactory;
use App\Factory\UserFactory;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use App\Service\GameService;
use App\Service\GameStateEvaluator;
use App\Service\ShipPlacer;
use App\Service\ShotProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(GameStateEvaluator::class)]
class GameStateEvaluatorTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private Board $boardPlayer1;
    private Board $boardPlayer2;
    private User $player1;
    private User $player2;
    private BoardRepository $boardRepository;
    private GameService $gameService;
    private ShipPlacer $shipPlacer;
    private ShotProcessor $shotProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = $this->getContainer();

        $this->gameService = $container->get(GameService::class);
        $this->shipPlacer = $container->get(ShipPlacer::class);
        $this->shotProcessor = $container->get(ShotProcessor::class);

        $userFactory = $container->get(UserFactory::class);
        $userRepository = $container->get(UserRepository::class);
        $this->player1 = $userFactory->create('player1@example.com', 'password');
        $userRepository->save($this->player1, true);
        $this->player2 = $userFactory->create('player2@example.com', 'password');
        $userRepository->save($this->player2, true);


        $this->game = $this->gameService->createNewGame($this->player1);

        $this->gameService->joinGame($this->game, $this->player2);

        $boardFactory = $this->getContainer()->get(BoardFactory::class);
        $this->boardRepository = $container->get(BoardRepository::class);
        $this->boardPlayer1 = $boardFactory->create($this->game, $this->player1);
        $this->boardPlayer2 = $boardFactory->create($this->game, $this->player2);
        $this->boardRepository->save($this->boardPlayer1, true);
        $this->boardRepository->save($this->boardPlayer2, true);
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
        $this->assertTrue($result);
    }
}
