<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Enum\GameStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UpdateLobbyService
{
    public function __construct(
        private MercureService         $mercureService,
        private UrlGeneratorInterface  $urlGenerator,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function updateLobby(Game $game): void
    {
        $playersReady = $game->getPlayersReady();
        switch (count($playersReady)) {
            case 1:
                $this->firstTime($game);
                break;
            case 2:
                $this->secondTime($game);
                break;
            default:
                throw new \Exception("Something went wrong");
        }
    }

    public function firstTime(Game $game)
    {
        $playersReady = $game->getPlayersReady();
        $readyPlayerUuid = reset($playersReady);
        $statusMsg = '';
        $player = -1;

        if ($readyPlayerUuid === $game->getPlayer1()->getId()) {
            $player = 1;
            $statusMsg = 'Player 1 has finished placing ships ';
        }

        if ($readyPlayerUuid === $game->getPlayer2()->getId()) {
            $player = 2;
            $statusMsg = ' Player 2 has finished placing ships ';
        }

        $this->mercureService->publishFirstPlayerPlacedShips($game, $player, $statusMsg);
    }

    public function secondTime(Game $game)
    {
        $game->setStatus(GameStatus::IN_PROGRESS);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $playersReady = $game->getPlayersReady();
        $readyPlayerUuid = end($playersReady);
        $statusMsg = '';
        $player = -1;

        if ($readyPlayerUuid === $game->getPlayer1()->getId()) {
            $player = 1;
            $statusMsg = 'Player 1 has finished placing ships ';
        }

        if ($readyPlayerUuid === $game->getPlayer2()->getId()) {
            $player = 2;
            $statusMsg = ' Player 2 has finished placing ships ';
        }
        $this->mercureService->publishSecondPlayerPlacedShips(
            $game, $player, $statusMsg,
            $this->urlGenerator->generate('app_game_play', ['id' => $game->getId()]));
    }
}