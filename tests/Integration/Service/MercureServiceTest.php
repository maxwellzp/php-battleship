<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Factory\GameEventFactory;
use App\Service\MercureService;
use App\Tests\Helper\GameTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(MercureService::class)]
class MercureServiceTest extends KernelTestCase
{
    use GameTestTrait;
    use ResetDatabase;
    use GameTestTrait;

    protected function setUp(): void
    {
        $this->bootGameTestKernel();
        $this->initializeGameWithPlayersAndBoards([]);
    }

    public function testPublishNewGame(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $service = new MercureService($hub);

        $joinPath = '/game/' . $this->game->getId() . '/join';

        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) use ($joinPath) {
                // Check topic
                $topics = $update->getTopics();
                if (!in_array('http://example.com/new-game', $topics, true)) {
                    return false;
                }

                // Decode data
                $data = json_decode($update->getData(), true);
                if (!is_array($data)) {
                    return false;
                }

                return $data['gameId'] === $this->game->getId()->toString()
                    && $data['player1'] === $this->game->getPlayer1()->getUsername()
                    && $data['status'] === $this->game->getStatus()->value
                    && $data['joinPath'] === $joinPath;
            }));

        $service->publishNewGame($this->game, $joinPath);
    }

    public function testPublishJoinedGame(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $service = new MercureService($hub);

        $shipPlacementUrl = '/game/' . $this->game->getId() . '/place-ships';
        $expectedData = [
            'player2Username' => $this->game->getPlayer2()->getUsername(),
            'status' => $this->game->getStatus()->value,
            'shipPlacementUrl' => $shipPlacementUrl,
        ];

        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) use ($expectedData) {
                return $update->getTopics()[0] === 'http://example.com/update-lobby/' . $this->game->getId()
                    && json_decode($update->getData(), true) === $expectedData;
            }));

        $service->publishJoinedGame($this->game, $shipPlacementUrl);
    }

    public function testPublishGameEvent(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $service = new MercureService($hub);

        $factory = new GameEventFactory();
        $event = $factory->create($this->game, 'Player1 joined the game');

        $expectedData = ['message' => $event->getMessage()];

        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) use ($event, $expectedData) {
                return $update->getTopics()[0] === 'http://example.com/game-logs/' . $event->getGame()->getId()
                    && json_decode($update->getData(), true) === $expectedData;
            }));

        $service->publishGameEvent($event);
    }

    public function testPublishFirstPlayerPlacedShips(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $service = new MercureService($hub);

        $player = 1;
        $statusMsg = 'Player 1 has placed all ships';
        $expectedData = [
            'status' => 'one_player_ready',
            'player' => $player,
            'statusMsg' => $statusMsg,
        ];

        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) use ($expectedData) {
                return $update->getTopics()[0] === 'http://example.com/update-lobby/' . $this->game->getId()
                    && json_decode($update->getData(), true) === $expectedData;
            }));

        $service->publishFirstPlayerPlacedShips($this->game, $player, $statusMsg);
    }

    public function testPublishSecondPlayerPlacedShips(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $service = new MercureService($hub);

        $player = 2;
        $statusMsg = 'Player 2 is ready';
        $gameStartUrl = '/game/' . $this->game->getId() . '/start';

        $expectedData = [
            'status' => $this->game->getStatus()->value,
            'player' => $player,
            'statusMsg' => $statusMsg,
            'gameStartUrl' => $gameStartUrl,
        ];

        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) use ($expectedData) {
                return $update->getTopics()[0] === 'http://example.com/update-lobby/' . $this->game->getId()
                    && json_decode($update->getData(), true) === $expectedData;
            }));

        $service->publishSecondPlayerPlacedShips(
            $this->game,
            $player,
            $statusMsg,
            $gameStartUrl
        );
    }
}
