<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Repository\ShipRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        $newGame->setCurrentTurn($user); // TODO: Add random choice
        $newGame->setStatus(GameStatus::WAITING_FOR_ANOTHER_PLAYER);
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
        if ($game->getPlayer2() || $game->getPlayer1() === $user) {
            return $this->redirectToRoute('app_game_index');
        }

        $game->setPlayer2($user);
        $game->setStatus(GameStatus::PLACING_SHIPS);
        // $game->setCurrentTurn($game->getPlayer1());
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
        $you = $user;
        $yourBoard = $boardRepository->findOneBy([
            'player' => $user,
            'game' => $game,
        ]);
        $yourShips = $shipRepository->findBy([
            'board' => $yourBoard
        ]);

        $yourBoardInfo = [];
        foreach ($yourShips as $ship) {
            foreach ($ship->getCoordinates() as $coordinate) {
                $position = $coordinate->x . $coordinate->y;
                $yourBoardInfo[$position] = 'ship';
            }
        }

        foreach($yourBoard->getShots() as $shot) {
            $position = $shot->getX() . $shot->getY();
            $yourBoardInfo[$position] = 'hit';
        }








        $opponent = $game->getPlayer1() === $user ? $game->getPlayer2() : $game->getPlayer1();
        $opponentBoard = $boardRepository->findOneBy([
            'player' => $opponent,
            'game' => $game,
        ]);
        $opponentShips = $shipRepository->findBy([
            'board' => $opponentBoard
        ]);


        $opponentBoardInfo = [];
//        foreach ($opponentShips as $ship) {
//            foreach ($ship->getCoordinates() as $coordinate) {
//                $position = $coordinate->x . $coordinate->y;
//                $opponentBoardInfo[$position] = 'ship';
//            }
//        }

        foreach($opponentBoard->getShots() as $shot) {
            $position = $shot->getX() . $shot->getY();
            $opponentBoardInfo[$position] = 'hit';
        }



        return $this->render('/game/play.html.twig', [
            'game' => $game,
            'opponent' => $opponent,
            'opponentBoardInfo' => $opponentBoardInfo,
            'yourBoardInfo' => $yourBoardInfo
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
        EntityManagerInterface $entityManager
    ): Response
    {
        $playerOpponent = $game->getPlayer1() === $user ? $game->getPlayer2() : $game->getPlayer1();

        $game->setWinner($playerOpponent);
        $game->setStatus(GameStatus::GAME_FINISHED);
        $game->setFinishedAt(new \DateTimeImmutable());

        $entityManager->persist($game);
        $entityManager->flush();

        return $this->redirectToRoute('app_game_lobby', [
            'id' => $game->getId(),
        ]);
    }
}
