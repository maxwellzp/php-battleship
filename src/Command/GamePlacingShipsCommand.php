<?php

declare(strict_types=1);

namespace App\Command;

use App\DTO\ShipDTO;
use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Exception\InvalidPlacementException;
use App\Factory\BoardFactory;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\ShipPlacer;
use App\Service\UpdateLobbyService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'game:placing-ships',
    description: 'Add a short description for your command',
)]
class GamePlacingShipsCommand extends Command
{
    public function __construct(
        private BoardFactory $boardFactory,
        private UserRepository $userRepository,
        private BoardRepository $boardRepository,
        private GameRepository $gameRepository,
        private EntityManagerInterface $entityManager,
        private ShipPlacer $shipPlacer,
        private LoggerInterface $logger,
        private UpdateLobbyService $updateLobbyService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $game = $this->gameRepository->find('0196770e-078f-7f33-bf9f-a02e41992a1f');
        $player = $this->userRepository->findOneBy(['email' => 'player1@example.com']);

        $board = $this->boardRepository->findOneBy(['game' => $game, 'player' => $player]);
        if (!$board) {
            $board = $this->boardFactory->create($game, $player);
            $this->boardRepository->save($board);
        }

        $requestJson = '
        [
   {
      "name":"Carrier",
      "orientation":"horizontal",
      "coords":[
         {
            "x":2,
            "y":1
         },
         {
            "x":3,
            "y":1
         },
         {
            "x":4,
            "y":1
         },
         {
            "x":5,
            "y":1
         },
         {
            "x":6,
            "y":1
         }
      ]
   },
   {
      "name":"Battleship",
      "orientation":"vertical",
      "coords":[
         {
            "x":8,
            "y":4
         },
         {
            "x":8,
            "y":5
         },
         {
            "x":8,
            "y":6
         },
         {
            "x":8,
            "y":7
         }
      ]
   },
   {
      "name":"Cruiser",
      "orientation":"vertical",
      "coords":[
         {
            "x":2,
            "y":7
         },
         {
            "x":2,
            "y":8
         },
         {
            "x":2,
            "y":9
         }
      ]
   },
   {
      "name":"Submarine",
      "orientation":"horizontal",
      "coords":[
         {
            "x":4,
            "y":4
         },
         {
            "x":5,
            "y":4
         },
         {
            "x":6,
            "y":4
         }
      ]
   },
   {
      "name":"Destroyer",
      "orientation":"horizontal",
      "coords":[
         {
            "x":8,
            "y":9
         },
         {
            "x":9,
            "y":9
         }
      ]
   },
   {
      "name":"Destroyer",
      "orientation":"vertical",
      "coords":[
         {
            "x":9,
            "y":0
         },
         {
            "x":9,
            "y":1
         }
      ]
   }
]
        ';

        $payload = json_decode($requestJson, true);


        /** @var ShipDTO[] $ships */
        $shipsDto = [];
        foreach ($payload as $ship) {
            $shipDto = new ShipDTO(ShipType::from($ship['name']), ShipOrientation::from($ship['orientation']), $ship['coords']);
            $shipsDto[] = $shipDto;
        }

        try {
            $this->shipPlacer->isShipsValid($board, $shipsDto);
        } catch (InvalidPlacementException $exception) {
            $this->logger->error($exception->getMessage());
            return Command::FAILURE;
        }

        $this->shipPlacer->placeShips($board, $shipsDto);

        // Update Game
        $game->addPlayerReady($player->getId());
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        //Mercure update
        try {
            $this->updateLobbyService->updateLobby($game);
        }catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return Command::SUCCESS;
    }
}
