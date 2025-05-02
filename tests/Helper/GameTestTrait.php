<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\DTO\ShipDTO;
use App\Entity\Board;
use App\Entity\Game;
use App\Entity\User;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Factory\BoardFactory;
use App\Factory\UserFactory;
use App\Repository\BoardRepository;
use App\Repository\ShipRepository;
use App\Repository\ShotRepository;
use App\Repository\UserRepository;
use App\Service\BoardCreator;
use App\Service\GameService;
use App\Service\ShipPlacer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

trait GameTestTrait
{
    protected GameService $gameService;
    protected ShipPlacer $shipPlacer;
    protected BoardFactory $boardFactory;
    protected BoardRepository $boardRepository;
    protected UserFactory $userFactory;
    protected UserRepository $userRepository;
    protected ShotRepository $shotRepository;
    protected ShipRepository $shipRepository;

    protected User $player1;
    protected User $player2;
    protected Game $game;
    protected Board $boardPlayer1;
    protected Board $boardPlayer2;
    protected BoardCreator $boardCreator;

    protected function bootGameTestKernel(): void
    {
        static::bootKernel();
        $container = static::getContainer();

        $this->gameService = $container->get(GameService::class);
        $this->boardCreator = $container->get(BoardCreator::class);
        $this->shipPlacer = $container->get(ShipPlacer::class);
        $this->boardFactory = $container->get(BoardFactory::class);
        $this->boardRepository = $container->get(BoardRepository::class);
        $this->userFactory = $container->get(UserFactory::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->shotRepository = $container->get(ShotRepository::class);
        $this->shipRepository = $container->get(ShipRepository::class);
    }

    /**
     * Bootstraps a standard game with two players and optional ship placement.
     *
     * @param ShipDTO[]|null $ships Ship definitions to place on both boards, or null to use default ships.
     *                              Pass empty array [] to skip placement.
     */
    protected function initializeGameWithPlayersAndBoards(?array $ships = null): void
    {
        $this->player1 = $this->userFactory->create('player1@example.com', 'password');
        $this->userRepository->save($this->player1, true);

        $this->player2 = $this->userFactory->create('player2@example.com', 'password');
        $this->userRepository->save($this->player2, true);

        $this->game = $this->gameService->createNewGame($this->player1);
        $this->game = $this->gameService->joinGame($this->game, $this->player2);

        [$this->boardPlayer1, $this->boardPlayer2] = $this->boardCreator->createBoards($this->game);

        if ($ships === null) {
            $ships = [$this->getDefaultSubmarine()];
        }

        if (!empty($ships)) {
            $this->shipPlacer->placeShips($this->boardPlayer1, $ships);
            $this->shipPlacer->placeShips($this->boardPlayer2, $ships);
        }
    }

    protected function getDefaultSubmarine(): ShipDTO
    {
        return new ShipDTO(
            ShipType::SUBMARINE,
            ShipOrientation::VERTICAL,
            [
                ['x' => 0, 'y' => 0],
                ['x' => 0, 'y' => 1],
                ['x' => 0, 'y' => 2],
            ]
        );
    }

    /**
     * @return ShipDTO[] array
     */
    public function getShips(): array
    {
        return [
            new ShipDTO(ShipType::CARRIER, ShipOrientation::HORIZONTAL, [
                ["x" => 2, "y" => 1],
                ["x" => 3, "y" => 1],
                ["x" => 4, "y" => 1],
                ["x" => 5, "y" => 1],
                ["x" => 6, "y" => 1],
            ]),
            new ShipDTO(ShipType::BATTLESHIP, ShipOrientation::VERTICAL, [
                ["x" => 8, "y" => 4],
                ["x" => 8, "y" => 5],
                ["x" => 8, "y" => 6],
                ["x" => 8, "y" => 7],
            ]),
            new ShipDTO(ShipType::CRUISER, ShipOrientation::VERTICAL, [
                ["x" => 2, "y" => 7],
                ["x" => 2, "y" => 8],
                ["x" => 2, "y" => 9],
            ]),
            new ShipDTO(ShipType::SUBMARINE, ShipOrientation::HORIZONTAL, [
                ["x" => 4, "y" => 4],
                ["x" => 5, "y" => 4],
                ["x" => 6, "y" => 4],
            ]),
            new ShipDTO(ShipType::DESTROYER, ShipOrientation::HORIZONTAL, [
                ["x" => 8, "y" => 9],
                ["x" => 9, "y" => 9],
            ]),
            new ShipDTO(ShipType::DESTROYER, ShipOrientation::VERTICAL, [
                ["x" => 9, "y" => 0],
                ["x" => 9, "y" => 1],
            ]),
        ];
    }
}
