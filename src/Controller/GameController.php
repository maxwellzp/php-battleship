<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Board;
use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Factory\BoardFactory;
use App\Factory\ShipFactory;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class GameController extends AbstractController
{
    #[Route('/', name: 'app_game_index', methods: ['GET'])]
    public function index(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findAll();
        return $this->render('game/index.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/game', name: 'app_game_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createGame(GameRepository $gameRepository): Response
    {
        $newGame = new Game();
        $newGame->setPlayer1($this->getUser());
        $newGame->setStatus(GameStatus::WAITING);
        $newGame->setCreatedAt(new \DateTimeImmutable());
        $gameRepository->save($newGame, true);

        return $this->redirectToRoute('app_game_lobby', [
            'id' => $newGame->getId(),
        ]);
    }

    #[Route('/game/{id}/lobby', name: 'app_game_lobby', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function lobby(Game $game): Response
    {
        return $this->render('/game/lobby.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/game/{id}/join', name: 'app_game_join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function joinGame(
        #[CurrentUser] User $user,
        Game $game,
        GameRepository $gameRepository
    ): Response
    {
        $game->setPlayer2($user);
        $game->setStatus(GameStatus::ACTIVE);
        $gameRepository->save($game, true);

        return $this->redirectToRoute('app_game_lobby', [
            'id' => $game->getId(),
        ]);
    }

    #[Route('/game/{id}/ship-placement', name: 'app_game_ship_placement', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function shipPlacement(
        #[CurrentUser] User $user,
        Game $game,
    ): Response
    {
        return $this->render('/game/ship_placement.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/game/{id}/ship-placement', name: 'app_game_ship_placement_save', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function shipPlacementSave(
        #[CurrentUser] User $user,
        Game $game,
        LoggerInterface $logger,
        Request $request,
        ShipFactory $shipFactory,
        BoardFactory $boardFactory,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $board = $boardFactory->create($game);
        $entityManager->persist($board);
        $entityManager->flush();

        /*
         [
   {
      "name":"Carrier",
      "orientation":"horizontal",
      "coords":[
         {
            "x":3,
            "y":0
         },
         {
            "x":4,
            "y":0
         },
         {
            "x":5,
            "y":0
         },
         {
            "x":6,
            "y":0
         },
         {
            "x":7,
            "y":0
         }
      ]
   },
   {
      "name":"Battleship",
      "orientation":"horizontal",
      "coords":[
         {
            "x":5,
            "y":5
         },
         {
            "x":6,
            "y":5
         },
         {
            "x":7,
            "y":5
         },
         {
            "x":8,
            "y":5
         }
      ]
   },
   {
      "name":"Cruiser",
      "orientation":"horizontal",
      "coords":[
         {
            "x":0,
            "y":3
         },
         {
            "x":1,
            "y":3
         },
         {
            "x":2,
            "y":3
         }
      ]
   },
   {
      "name":"Submarine",
      "orientation":"horizontal",
      "coords":[
         {
            "x":1,
            "y":7
         },
         {
            "x":2,
            "y":7
         },
         {
            "x":3,
            "y":7
         }
      ]
   },
   {
      "name":"Destroyer",
      "orientation":"horizontal",
      "coords":[
         {
            "x":6,
            "y":8
         },
         {
            "x":7,
            "y":8
         }
      ]
   },
   {
      "name":"Destroyer",
      "orientation":"horizontal",
      "coords":[
         {
            "x":7,
            "y":2
         },
         {
            "x":8,
            "y":2
         }
      ]
   }
]
         */


        foreach ($data as $placement) {
            $ship = $shipFactory->create(
                $board,
                ShipType::from($placement['name']),
                ShipOrientation::from($placement['orientation']),
                $placement['coords']);
            $entityManager->persist($ship);
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/game/{id}/play', name: 'game_play')]
    public function play(Game $game): Response
    {
        return $this->render('/game/play.html.twig', [
            'game' => $game,
        ]);
    }

    public function markPlayerAsReady(
        #[CurrentUser] User $user,
        GameRepository $gameRepository,
        Game $game): Response
    {
        $game->addPlayerReady($user->getId());
        $gameRepository->save($game, true);

        return new Response();
    }

    public function unMarkPlayerAsReady(
        #[CurrentUser] User $user,
        GameRepository $gameRepository,
        Game $game): Response
    {
        $game->removePlayerReady($user->getId());
        $gameRepository->save($game, true);

        return new Response();
    }

}
