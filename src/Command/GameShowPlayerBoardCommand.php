<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Board;
use App\Entity\Game;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Repository\ShipRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'game:show-player-board',
    description: 'Show player board',
)]
class GameShowPlayerBoardCommand extends Command
{
    public function __construct(
        private readonly UserRepository  $userRepository,
        private readonly GameRepository  $gameRepository,
        private readonly BoardRepository $boardRepository,
        private readonly ShipRepository  $shipRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');


        $player = $this->userRepository->findOneBy(['email' => $email]);

        $game = $this->gameRepository->findOneBy([
            'player1' => $player,
        ]);

        if (!$game instanceof Game) {
            $io->error('Game not found');
            return Command::FAILURE;
        }

        $boardPlayer = $this->boardRepository->findOneBy([
            'game' => $game,
            'player' => $player,
        ]);

        if (!$boardPlayer instanceof Board) {
            $io->error('Board not found');
            return Command::FAILURE;
        }

        $ships = $this->shipRepository->findBy([
            'board' => $boardPlayer,
        ]);

        foreach ($ships as $ship) {
            $io->writeln(sprintf("Ship size: %d", $ship->getSize()));
            $io->writeln(sprintf("Ship type: %s", $ship->getType()->value));
            $io->writeln(sprintf("Orientation: %s", $ship->getOrientation()->value));
            $io->writeln(sprintf("Coordinates: %s", json_encode($ship->getCoordinates())));
            $io->writeln(sprintf("Is sunk?: %s", $ship->isSunk() ? 'yes' : 'no'));
            $io->writeln("-----------------------------------------------------------");
        }

        return Command::SUCCESS;
    }
}
