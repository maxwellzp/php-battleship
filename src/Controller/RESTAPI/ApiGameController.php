<?php

declare(strict_types=1);

namespace App\Controller\RESTAPI;

use App\DTO\CoordinateDTO;
use App\DTO\ShipDTO;
use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\User;
use App\Exception\InvalidPlacementException;
use App\Factory\BoardFactory;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Service\GameService;
use App\Service\GameStateEvaluator;
use App\Service\ShipPlacer;
use App\Service\ShipStatusService;
use App\Service\ShotProcessor;
use App\Service\UpdateLobbyService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ApiGameController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param User $user
     * @param Game $game
     * @param BoardFactory $boardFactory
     * @param ShipDTO[] $ships
     * @param LoggerInterface $logger
     * @param ShipPlacer $shipPlacer
     * @param BoardRepository $boardRepository
     * @param GameRepository $gameRepository
     * @param UpdateLobbyService $updateLobbyService
     * @return JsonResponse
     */
    #[Route('api/game/{id}/ship-placement', name: 'app_game_ship_placement_save', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function shipPlacementSave(
        #[CurrentUser] User $user,
        Game $game,
        #[MapRequestPayload(type: ShipDTO::class)] array $ships,
        LoggerInterface $logger,
        ShipPlacer $shipPlacer,
        BoardRepository $boardRepository,
        GameRepository $gameRepository,
        UpdateLobbyService $updateLobbyService,
    ): JsonResponse {
        if (!in_array($user, [$game->getPlayer1(), $game->getPlayer2()], true)) {
            throw $this->createAccessDeniedException();
        }

        $board = $boardRepository->findOneBy(['game' => $game, 'player' => $user]);

        try {
            $shipPlacer->isShipsValid($board, $ships);
        } catch (InvalidPlacementException $exception) {
            $logger->error($exception->getMessage());
            return new JsonResponse([
                'error' => $exception->getMessage()
            ]);
        }

        $shipPlacer->placeShips($board, $ships);

        // Update Game
        $game->addPlayerReady($user->getId());
        $gameRepository->save($game, true);

        //Mercure update
        try {
            $updateLobbyService->updateLobby($game);
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }
        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @param Game $game
     * @param User $user
     * @param EntityManagerInterface $em
     * @param CoordinateDTO $shotCoordinates
     * @param BoardRepository $boardRepository
     * @param ShotProcessor $shotProcessor
     * @param GameStateEvaluator $gameStateEvaluator
     * @param GameService $gameService
     * @param LoggerInterface $logger
     * @return JsonResponse
     */
    #[Route('/api/game/{id}/fire', name: 'api_game_fire', methods: ['POST'])]
    public function fire(
        Game $game,
        #[CurrentUser] User $user,
        EntityManagerInterface $em,
        #[MapRequestPayload] CoordinateDTO $shotCoordinates,
        BoardRepository $boardRepository,
        ShotProcessor $shotProcessor,
        GameStateEvaluator $gameStateEvaluator,
        GameService $gameService,
        LoggerInterface $logger,
        ShipStatusService $shipStatusService
    ): JsonResponse {
        if (!in_array($user, [$game->getPlayer1(), $game->getPlayer2()], true)) {
            throw $this->createAccessDeniedException();
        }

        if ($game->getCurrentTurn() !== $user) {
            return new JsonResponse(['error' => 'Not your turn'], 403);
        }

        $opponent = $game->getOpponent($user);
        $opponentBoard = $boardRepository->findOneBy([
            'game' => $game,
            'player' => $opponent,
        ]);

        try {
            $shot = $shotProcessor->processShot($opponentBoard, $user, $shotCoordinates->x, $shotCoordinates->y);
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
            $logger->error($exception->getTraceAsString());
            return new JsonResponse([
                'error' => $exception->getMessage()
            ]);
        }

        $ship = $opponentBoard->findShipAtPosition($shotCoordinates->x, $shotCoordinates->y);

        if ($ship instanceof Ship) {
            $shipStatusService->updateShipSunkStatus($ship);
        }

        if ($gameStateEvaluator->isGameOver($game) && $winner = $gameStateEvaluator->getWinner($game)) {
            $gameService->finishGame($game, $winner);
            return new JsonResponse([
                'winner' => $winner->getUsername()
            ]);
        }

        $game->setCurrentTurn($opponent);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'result' => $shot->getResult()->value,
            'x' => $shotCoordinates->x,
            'y' => $shotCoordinates->y,
        ]);
    }
}
