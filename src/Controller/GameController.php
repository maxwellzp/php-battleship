<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    ): Response
    {
        #TODO save board coordinates
        return $this->redirectToRoute('app_game_ship_placement');
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
