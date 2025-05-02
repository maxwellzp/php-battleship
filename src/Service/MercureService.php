<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\GameEvent;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureService
{
    private const TOPIC_NEW_GAME = 'http://example.com/new-game';
    private const TOPIC_UPDATE_LOBBY_PREFIX = 'http://example.com/update-lobby/';
    private const TOPIC_GAME_LOG_PREFIX = 'http://example.com/game-logs/';

    public function __construct(private readonly HubInterface $hub)
    {
    }

    public function publishNewGame(Game $game, string $joinPath): void
    {
        $update = new Update(
            self::TOPIC_NEW_GAME,
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
        $this->publishLobbyUpdate($game, [
            'player2Username' => $game->getPlayer2()->getUsername(),
            'status' => $game->getStatus()->value,
            'shipPlacementUrl' => $shipPlacementUrl,
        ]);
    }

    public function publishGameEvent(GameEvent $gameEvent): void
    {
        $update = new Update(
            self::TOPIC_GAME_LOG_PREFIX . $gameEvent->getGame()->getId(),
            json_encode([
                'message' => $gameEvent->getMessage(),
            ])
        );

        $this->hub->publish($update);
    }

    public function publishFirstPlayerPlacedShips(Game $game, int $player, string $statusMsg): void
    {
        $this->publishLobbyUpdate($game, [
            'status' => 'one_player_ready',
            'player' => $player,
            'statusMsg' => $statusMsg,
        ]);
    }

    public function publishSecondPlayerPlacedShips(
        Game   $game,
        int    $player,
        string $statusMsg,
        string $gameStartUrl
    ): void
    {
        $this->publishLobbyUpdate($game, [
            'status' => $game->getStatus()->value,
            'player' => $player,
            'statusMsg' => $statusMsg,
            'gameStartUrl' => $gameStartUrl,
        ]);
    }

    private function publishLobbyUpdate(Game $game, array $payload): void
    {
        $update = new Update(
            self::TOPIC_UPDATE_LOBBY_PREFIX . $game->getId(),
            json_encode($payload)
        );

        $this->hub->publish($update);
    }
}
