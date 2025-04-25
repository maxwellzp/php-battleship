<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use App\Repository\BoardRepository;
use App\Repository\GameLogRepository;
use App\Repository\GameRepository;
use App\Repository\ShipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
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
        GameRepository $gameRepository,
        HubInterface $hub
    ): Response
    {
        $newGame = new Game();
        $newGame->setPlayer1($user);
        $newGame->setCurrentTurn($user); // TODO: Add random choice
        $newGame->setStatus(GameStatus::WAITING_FOR_ANOTHER_PLAYER);
        $newGame->setCreatedAt(new \DateTimeImmutable());
        $gameRepository->save($newGame, true);

        $update = new Update(
            'http://example.com/new-game',
            json_encode([
                'gameId' => $newGame->getId(),
                'player1' => $newGame->getPlayer1()->getUsername(),
                'status' => $newGame->getStatus()->value,
                'createdAt' => $newGame->getCreatedAt()->format('Y-m-d H:i:s'),
                'joinPath' => $this->generateUrl('app_game_join', ['id' => $newGame->getId()]),
            ])
        );

        $hub->publish($update);

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
        GameRepository $gameRepository,
        HubInterface $hub
    ): Response
    {
        if ($game->getPlayer2() || $game->getPlayer1() === $user) {
            return $this->redirectToRoute('app_game_index');
        }

        $game->setPlayer2($user);
        $game->setStatus(GameStatus::PLACING_SHIPS);
        // $game->setCurrentTurn($game->getPlayer1());
        $gameRepository->save($game, true);



        $update = new Update(
            'http://example.com/update-lobby/' . $game->getId(),
            json_encode([
                'player2Username' => $game->getPlayer2()->getUsername(),
                'status' => $game->getStatus()->value,
                'shipPlacementUrl' => $this->generateUrl('app_game_ship_placement', ['id' => $game->getId()]),
            ])
        );

        $hub->publish($update);


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
        ShipRepository $shipRepository,
        GameLogRepository $gameLogRepository,
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

        foreach($opponentBoard->getShots() as $shot) {
            $position = $shot->getX() . $shot->getY();
            $opponentBoardInfo[$position] = 'hit';
        }

        $gameLogs = $gameLogRepository->findBy([
            'game' => $game,
        ]);


        return $this->render('/game/play.html.twig', [
            'game' => $game,
            'opponent' => $opponent,
            'opponentBoardInfo' => $opponentBoardInfo,
            'yourBoardInfo' => $yourBoardInfo,
            'gameLogs' => $gameLogs
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
