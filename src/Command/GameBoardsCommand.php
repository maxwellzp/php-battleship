<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\BoardViewService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'game:boards',
    description: 'Add a short description for your command',
)]
class GameBoardsCommand extends Command
{
    public function __construct(
        private BoardViewService $boardViewService,
        private UserRepository $userRepository,
        private GameRepository $gameRepository,
    ) {
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
        if (!$game) {
            $io->error('Game not found');
            return Command::FAILURE;
        }

        $currentUser = $this->userRepository->findOneBy(['email' => 'player1@example.com']);
        if (!$currentUser) {
            $io->error('Current user not found');
        }

        // {"x":5,"y":4,"ship":"Submarine","hit":false,"miss":false,"sunk":false}
        $yourBoard = $this->boardViewService->getBoardForPlayer($game, $currentUser, true);

        // {"x":5,"y":2,"ship":null,"hit":false,"miss":false,"sunk":false}
        $enemyBoard = $this->boardViewService->getBoardForPlayer($game, $currentUser, false);


        for ($y = 0; $y < 10; $y++) {
            for ($x = 0; $x < 10; $x++) {
                $cell = $yourBoard[$y][$x];
                $x = $cell['x'];
                $y = $cell['y'];
                $ship = $cell['ship'];
                $io->write($ship ? '[ S ] ' : '[' . $x . ':' . $y . '] ');
            }
            $io->write("\n");
        }

        $io->writeln("---------------------------------");

        for ($y = 0; $y < 10; $y++) {
            for ($x = 0; $x < 10; $x++) {
                $io->write($enemyBoard[$y][$x]['hit'] ? '[H]' : '[ ]');
            }
            $io->write("\n");
        }

        return Command::SUCCESS;
    }
}
