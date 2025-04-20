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
    public function createGame(GameRepository $gameRepository): Response
    {
        $newGame = new Game();
        $newGame->setPlayer1($this->getUser());
        $newGame->setStatus(GameStatus::WAITING);
        $newGame->setCreatedAt(new \DateTimeImmutable());
        $gameRepository->save($newGame, true);

        return new Response();
    }

    #[Route('/game/{id}', name: 'app_game_join', methods: ['GET'])]
    public function joinGame(Game $game): Response
    {

        return new Response();
    }

    #[Route('/game/{id}/play', name: 'game_play')]
    public function play(Game $game): Response
    {

        return new Response();
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
