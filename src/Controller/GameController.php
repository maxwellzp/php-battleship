<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\GameEventRepository;
use App\Repository\GameRepository;
use App\Service\BoardViewService;
use App\Service\GameService;
use App\Service\MercureService;
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
    public function createGame(
        #[CurrentUser] User $user,
        GameService $gameService,
        MercureService $mercureService
    ): Response
    {
        $game = $gameService->createNewGame($user);
        $mercureService->publishNewGame($game, $this->generateUrl('app_game_join', ['id' => $game->getId()]));

        return $this->redirectToRoute('app_game_lobby', [
            'id' => $game->getId(),
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
        GameService $gameService,
        MercureService $mercureService
    ): Response
    {
        if ($game->getPlayer2() || $game->getPlayer1() === $user) {
            return $this->redirectToRoute('app_game_index');
        }
        $game = $gameService->joinGame($game, $user);
        $mercureService->publishJoinedGame($game, $this->generateUrl('app_game_ship_placement', ['id' => $game->getId()]));

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

    #[Route('/game/{id}/play', name: 'app_game_play', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function play(
        #[CurrentUser] User $user,
        Game $game,
        GameEventRepository $gameEventRepository,
        BoardViewService $boardViewService
    ): Response
    {
        $opponent = $game->getPlayer1() === $user ? $game->getPlayer2() : $game->getPlayer1();

        // {"x":5,"y":4,"ship":"Submarine","hit":false,"miss":false,"sunk":false}
        // Show full ships + hits + misses (because it's your own board, no secrets)
        $yourBoard = $boardViewService->getBoardForPlayer($game, $user, true);

        // {"x":5,"y":2,"ship":null,"hit":false,"miss":false,"sunk":false}
        // Show only hits/misses (no enemy ships visible unless hit)
        $opponentBoard = $boardViewService->getBoardForPlayer($game, $user, false);

        $gameEvents = $gameEventRepository->findBy([
            'game' => $game,
        ]);

        return $this->render('/game/play.html.twig', [
            'game' => $game,
            'opponent' => $opponent,
            'yourBoard' => $yourBoard,
            'opponentBoard' => $opponentBoard,
            'gameLogs' => $gameEvents
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

    #[Route('/game/{id}/surrender', name: 'app_game_surrender', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function surrender(
        #[CurrentUser] User $user,
        Game $game,
        GameService $gameService,
    ): Response
    {
        $playerOpponent = $game->getPlayer1() === $user ? $game->getPlayer2() : $game->getPlayer1();
        $gameService->finishGame($game, $playerOpponent);

        return $this->redirectToRoute('app_game_lobby', [
            'id' => $game->getId(),
        ]);
    }
}
