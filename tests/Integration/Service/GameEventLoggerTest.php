<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Game;
use App\Factory\GameEventFactory;
use App\Factory\GameFactory;
use App\Factory\UserFactory;
use App\Repository\GameEventRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\GameEventLogger;
use App\Service\MercureService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

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
        $userRepository->save($user);

        $gameFactory = $container->get(GameFactory::class);
        $gameRepository = $container->get(GameRepository::class);
        $this->game = $gameFactory->create($user);
        $gameRepository->save($this->game);

        $this->gameEventRepository = $container->get(GameEventRepository::class);
        $gameEventFactory = $container->get(GameEventFactory::class);
        $mercureService = $this->createMock(MercureService::class);

        $this->gameEventLogger = new GameEventLogger($gameEventFactory, $this->gameEventRepository, $mercureService);
    }

    public function testLog()
    {
        $expectedMsg = "player-player2@example.com attacked A1: hit";
        $gameEvent = $this->gameEventLogger->log($this->game, $expectedMsg);

        $gameEventDb = $this->gameEventRepository->find($gameEvent->getId());

        $this->assertNotNull($gameEventDb->getId());
        $this->assertSame($gameEvent->getId(), $gameEventDb->getId());
    }
}
