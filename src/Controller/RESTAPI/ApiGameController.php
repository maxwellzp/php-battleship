<?php

declare(strict_types=1);

namespace App\Controller\RESTAPI;

use App\DTO\CoordinateDTO;
use App\DTO\ShipDTO;
use App\Entity\Game;
use App\Entity\Shot;
use App\Entity\User;
use App\Enum\ShotResult;
use App\Factory\BoardFactory;
use App\Factory\ShipFactory;
use App\Repository\BoardRepository;
use App\Repository\ShotRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        #[MapRequestPayload(type: ShipDTO::class)] array $ships
    ): JsonResponse
    {
        if (!in_array($user, [$game->getPlayer1(), $game->getPlayer2()], true)) {
            throw $this->createAccessDeniedException();
        }

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
        $this->entityManager->persist($game);

        $entityManager->flush();

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


        $shot = new Shot();
        $shot->setBoard($opponentBoard);
        $shot->setPlayer($user);
        $shot->setX($shotCoordinates->x);
        $shot->setY($shotCoordinates->y);
        $shot->setFiredAt(new \DateTimeImmutable());
        $shot->setResult(ShotResult::HIT);

        $em->persist($shot);

        $game->setCurrentTurn($playerOpponent);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }
}
