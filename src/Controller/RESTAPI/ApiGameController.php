<?php

declare(strict_types=1);

namespace App\Controller\RESTAPI;

use App\DTO\CoordinateDTO;
use App\DTO\ShipDTO;
use App\Entity\Game;
use App\Entity\Shot;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Enum\ShotResult;
use App\Factory\BoardFactory;
use App\Factory\ShipFactory;
use App\Repository\BoardRepository;
use App\Repository\ShotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
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
     * @param ShipFactory $shipFactory
     * @param BoardFactory $boardFactory
     * @param EntityManagerInterface $entityManager
     * @param ShipDTO[] $ships
     * @return JsonResponse
     */
    #[Route('api/game/{id}/ship-placement', name: 'app_game_ship_placement_save', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function shipPlacementSave(
        #[CurrentUser] User                   $user,
        Game                                  $game,
        ShipFactory                           $shipFactory,
        BoardFactory                          $boardFactory,
        EntityManagerInterface                $entityManager,
        #[MapRequestPayload(type: ShipDTO::class)] array $ships,
        HubInterface $hub
    ): JsonResponse
    {
        if (!in_array($user, [$game->getPlayer1(), $game->getPlayer2()], true)) {
            throw $this->createAccessDeniedException();
        }

        // Validate and place
//        if (!$board->canPlaceShip($ship)) {
//            throw new BadRequestHttpException('Invalid ship placement');
//        }


        $board = $boardFactory->create($game, $user);
        $entityManager->persist($board);
        $entityManager->flush();

        foreach ($ships as $ship) {
            $ship = $shipFactory->create(
                $board,
                $ship->name,
                $ship->orientation,
                $ship->coords
            );
            $entityManager->persist($ship);
        }

        $game->addPlayerReady($user->getId());
        if (count($game->getPlayersReady()) == 2) {
            $game->setStatus(GameStatus::IN_PROGRESS);
        }
        $this->entityManager->persist($game);

        $entityManager->flush();

        $update = new Update(
            'http://example.com/update-lobby/' . $game->getId(),
            json_encode([
                'status' => $game->getStatus()->value,
                'gameStartUrl' => $this->generateUrl('app_game_play', ['id' => $game->getId()]),
            ])
        );

        $hub->publish($update);



        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @param Game $game
     * @param User $user
     * @param EntityManagerInterface $em
     * @param CoordinateDTO $shotCoordinates
     * @param BoardRepository $boardRepository
     * @param ShotRepository $shotRepository
     * @return JsonResponse
     */
    #[Route('/api/game/{id}/fire', name: 'api_game_fire', methods: ['POST'])]
    public function fire(
        Game $game,
        #[CurrentUser] User $user,
        EntityManagerInterface $em,

        #[MapRequestPayload] CoordinateDTO $shotCoordinates,

        BoardRepository $boardRepository,
        ShotRepository $shotRepository,

    ): JsonResponse
    {
        if (!in_array($user, [$game->getPlayer1(), $game->getPlayer2()], true)) {
            throw $this->createAccessDeniedException();
        }

        if ($game->getStatus() !== GameStatus::PLACING_SHIPS) {
            //TODO redirect to Lobby
        }

        if ($game->getCurrentTurn() !== $user) {
            return new JsonResponse(['error' => 'Not your turn'], 403);
        }

        $playerOpponent = $game->getPlayer1() === $user ? $game->getPlayer2() : $game->getPlayer1();

        $opponentBoard = $boardRepository->findOneBy([
            'game' => $game, 'player' => $playerOpponent
        ]);


        $existingShot = $shotRepository->findOneBy([
            'board' => $opponentBoard,
            'player' => $user,
            'x' => $shotCoordinates->x,
            'y' => $shotCoordinates->y,
        ]);

        if ($existingShot instanceof Shot) {
            return $this->json(['error' => 'Already fired there'], 400);
        }

        $shotResult = ShotResult::MISS;

        foreach ($opponentBoard->getShips() as $ship) {
            foreach ($ship->getCoordinates() as $coord) {
                if ($coord->x == $shotCoordinates->x && $coord->y == $shotCoordinates->y) {
                    $shotResult = ShotResult::HIT;
                }
            }
        }


        $shot = new Shot();
        $shot->setBoard($opponentBoard);
        $shot->setPlayer($user);
        $shot->setX($shotCoordinates->x);
        $shot->setY($shotCoordinates->y);
        $shot->setFiredAt(new \DateTimeImmutable());
        $shot->setResult($shotResult);

        $em->persist($shot);

        $game->setCurrentTurn($playerOpponent);
        $em->flush();


        if ($game->getWinner() instanceof User) {
            $game->setStatus(GameStatus::GAME_FINISHED);
            $game->setFinishedAt(new \DateTimeImmutable());
            $em->persist($game);
            $em->flush();
        }

        return $this->json(['status' => 'ok', 'hit' => $shotResult->value]);
    }
}
