<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Factory\GameEventFactory;
use App\Factory\GameFactory;
use App\Factory\UserFactory;
use App\Repository\GameEventRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\GameEventLogger;
use App\Service\MercureService;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(GameEventLogger::class)]
class GameEventLoggerTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private GameEventRepository $gameEventRepository;
    private GameEventLogger $gameEventLogger;
    private Game $game;
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $userFactory = $container->get(UserFactory::class);
        $user = $userFactory->create('player1@example.com', 'password');
        $userRepository->save($user, true);

        $gameFactory = $container->get(GameFactory::class);
        $gameRepository = $container->get(GameRepository::class);
        $this->game = $gameFactory->create($user);
        $gameRepository->save($this->game, true);

        $this->gameEventRepository = $container->get(GameEventRepository::class);
        $gameEventFactory = $container->get(GameEventFactory::class);
        $mercureService = $this->createMock(MercureService::class);
        $expectedMsg = "player-player2@example.com attacked A1: hit";
        $mercureService->expects($this->once())
            ->method('publishGameEvent')
            ->with($this->callback(function (GameEvent $event) use ($expectedMsg) {
                return $event->getMessage() === $expectedMsg;
            }));

        $this->gameEventLogger = new GameEventLogger($gameEventFactory, $this->gameEventRepository, $mercureService);
    }

    public function testItLogsAndPersistsGameEvent()
    {
        $expectedMsg = "player-player2@example.com attacked A1: hit";
        $gameEvent = $this->gameEventLogger->log($this->game, $expectedMsg);

        $gameEventDb = $this->gameEventRepository->find($gameEvent->getId());

        $this->assertNotNull($gameEventDb->getId());
        $this->assertSame($gameEvent->getId(), $gameEventDb->getId());
        $this->assertSame($expectedMsg, $gameEventDb->getMessage());
        $this->assertSame($this->game->getId(), $gameEventDb->getGame()->getId());
    }
}
