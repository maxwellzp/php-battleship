<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Repository\ShipRepository;
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
        GameRepository $gameRepository
    ): Response
    {
        $newGame = new Game();
        $newGame->setPlayer1($user);
        $newGame->setCurrentTurn($user);
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

    #[Route('/game/{id}/play', name: 'app_game_play', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function play(
        #[CurrentUser] User $user,
        Game $game,
        BoardRepository $boardRepository,
        ShipRepository $shipRepository
    ): Response
    {
        $player1 = $game->getPlayer1();
        $player2 = $game->getPlayer2();

        $board1 = $boardRepository->findOneBy([
            'player' => $player1,
            'game' => $game,
        ]);
        $board2 = $boardRepository->findOneBy([
            'player' => $player2,
            'game' => $game,
        ]);

        $ships1 = $shipRepository->findBy([
            'board' => $board1
        ]);

        $ships2 = $shipRepository->findBy([
            'board' => $board2
        ]);

        $opponent = $game->getPlayer1() === $user ? $game->getPlayer2() : $game->getPlayer1();

        $board = [];
        foreach ($ships1 as $ship) {
            foreach ($ship->getCoordinates() as $coordinate) {
                $position = $coordinate->x . $coordinate->y;
                $board[$position] = 'ship';
            }
        }

        foreach($board1->getShots() as $shot) {
            $position = $shot->getX() . $shot->getY();
            $board[$position] = 'hit';
        }


        return $this->render('/game/play.html.twig', [
            'game' => $game,
            'opponent' => $opponent,
            'board' => $board
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
