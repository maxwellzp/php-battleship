<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\GameEvent;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureService
{
    public function __construct(private readonly HubInterface $hub)
    {

    }

    public function publishNewGame(Game $game, string $joinPath): void
    {
        $update = new Update(
            'http://example.com/new-game',
            json_encode([
                'gameId' => $game->getId(),
                'player1' => $game->getPlayer1()->getUsername(),
                'status' => $game->getStatus()->value,
                'createdAt' => $game->getCreatedAt()->format('Y-m-d H:i:s'),
                'joinPath' => $joinPath,
            ])
        );
        $this->hub->publish($update);
    }

    public function publishJoinedGame(Game $game, string $shipPlacementUrl): void
    {
        $update = new Update(
            'http://example.com/update-lobby/' . $game->getId(),
            json_encode([
                'player2Username' => $game->getPlayer2()->getUsername(),
                'status' => $game->getStatus()->value,
                'shipPlacementUrl' => $shipPlacementUrl,
            ])
        );
        $this->hub->publish($update);
    }

    public function publishGameEvent(GameEvent $gameEvent): void
    {
        $update = new Update(
            'http://example.com/game-logs/' . $gameEvent->getGame()->getId(),
            json_encode([
                'message' => $gameEvent->getMessage(),
            ])
        );

        $this->hub->publish($update);
    }

    public function publishFirstPlayerPlacedShips(Game $game, int $player, string $statusMsg): void
    {
        $update = new Update(
            'http://example.com/update-lobby/' . $game->getId(),
            json_encode([
                'status' => 'one_player_ready',
                'player' => $player,
                'statusMsg' => $statusMsg
            ])
        );

        $this->hub->publish($update);
    }

    public function publishSecondPlayerPlacedShips(
        Game   $game,
        int    $player,
        string $statusMsg,
        string $gameStartUrl): void
    {
        $update = new Update(
            'http://example.com/update-lobby/' . $game->getId(),
            json_encode([
                'status' => $game->getStatus()->value,
                'player' => $player,
                'statusMsg' => $statusMsg,
                'gameStartUrl' => $gameStartUrl,
            ])
        );

        $this->hub->publish($update);
    }

}
