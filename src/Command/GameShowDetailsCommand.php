<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\GameRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'game:show-details',
    description: 'Show game details',
)]
class GameShowDetailsCommand extends Command
{
    public function __construct(private readonly GameRepository $gameRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('gameId', InputArgument::REQUIRED, 'Game ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $gameId = $input->getArgument('gameId');

        $game = $this->gameRepository->find($gameId);

        $io->writeln("Id: " . $game->getId());
        $io->writeln("Status: " . $game->getStatus()->value);
        $io->writeln("Winner: " . $game->getWinner()?->getEmail());
        $io->writeln("Player 1: " . $game->getPlayer1()?->getEmail());
        $io->writeln("Player 2: " . $game->getPlayer2()?->getEmail());
        $io->writeln("Created At: " . $game->getCreatedAt()->format('Y-m-d H:i:s'));
        $io->writeln("Players are ready: " . json_encode($game->getPlayersReady()));

        return Command::SUCCESS;
    }
}
