<?php

declare(strict_types=1);

namespace App\Controller\RESTAPI;

use App\DTO\CoordinateDTO;
use App\DTO\ShipDTO;
use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\User;
use App\Exception\InvalidPlacementException;
use App\Exception\InvalidShotException;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Service\GameService;
use App\Service\GameStateEvaluator;
use App\Service\MercureService;
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
    /**
     * @param User $user
     * @param Game $game
     * @param ShipDTO[] $ships
     * @param LoggerInterface $logger
     * @param ShipPlacer $shipPlacer
     * @param BoardRepository $boardRepository
     * @param GameRepository $gameRepository
     * @param UpdateLobbyService $updateLobbyService
     * @return JsonResponse
     * @throws InvalidPlacementException
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
            return ApiResponse::error('You are not participating in this game.', 403);
        }

        $board = $boardRepository->findOneBy(['game' => $game, 'player' => $user]);

        try {
            $shipPlacer->isShipsValid($board, $ships);
        } catch(InvalidPlacementException $exception) {
            $logger->error($exception->getMessage());
            return ApiResponse::error($exception->getMessage(), 403);
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
            $logger->error($exception->getTraceAsString());
            return ApiResponse::error("Something went wrong. Please try again later.", 500);
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
            $logger->error($exception->getTraceAsString());
        }
        return ApiResponse::success([]);
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
     * @param ShipStatusService $shipStatusService
     * @param MercureService $mercureService
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
        ShipStatusService $shipStatusService,
        MercureService $mercureService,
    ): JsonResponse {
        if (!in_array($user, [$game->getPlayer1(), $game->getPlayer2()], true)) {
            return ApiResponse::error('You are not participating in this game.', 403);
        }

        if ($game->getCurrentTurn() !== $user) {
            return ApiResponse::error("It's not your turn now.", 403);
        }

        $opponent = $game->getOpponent($user);
        $opponentBoard = $boardRepository->findOneBy([
            'game' => $game,
            'player' => $opponent,
        ]);

        try {
            $shot = $shotProcessor->processShot($opponentBoard, $user, $shotCoordinates->x, $shotCoordinates->y);
        } catch(InvalidShotException $exception) {
            $logger->error($exception->getMessage());
            return ApiResponse::error($exception->getMessage(), 403);
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
            $logger->error($exception->getTraceAsString());
            return ApiResponse::error("Something went wrong. Please try again later.", 500);
        }

        $mercureService->publishBoardUpdate(
            $game,
            $shotCoordinates->x,
            $shotCoordinates->y,
            $shot->getResult()->value,
            $user->getId()->toString()
        );

        $ship = $opponentBoard->findShipAtPosition($shotCoordinates->x, $shotCoordinates->y);

        if ($ship instanceof Ship) {
            $shipStatusService->updateShipSunkStatus($ship, $user);
        }

        if ($gameStateEvaluator->isGameOver($game) && $winner = $gameStateEvaluator->getWinner($game)) {
            $gameService->finishGame($game, $winner);
        }

        $game->setCurrentTurn($opponent);
        $em->flush();

        $sunkCoordinates = [];
        if ($ship && $ship->isSunk()) {
            $sunkCoordinates = $ship->getCoordinates();
        }

        return ApiResponse::success([
            'result' => $shot->getResult()->value,
            'x' => $shotCoordinates->x,
            'y' => $shotCoordinates->y,
            'sunkCoordinates' => $sunkCoordinates,
            'winner' => $game->getWinner() ? $winner->getUsername(): null,
        ]);
    }
}
