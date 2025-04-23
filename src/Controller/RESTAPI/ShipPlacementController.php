<?php

declare(strict_types=1);

namespace App\Controller\RESTAPI;

use App\DTO\ShipDTO;
use App\Entity\Game;
use App\Entity\User;
use App\Factory\BoardFactory;
use App\Factory\ShipFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ShipPlacementController extends AbstractController
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
}
