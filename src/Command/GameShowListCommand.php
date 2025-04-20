<?php

namespace App\Command;

use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'game:show-list',
    description: 'Add a short description for your command',
)]
class GameShowListCommand extends Command
{
    public function __construct(private GameRepository $gameRepository, private EntityManagerInterface $entityManager)
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


        $games = $this->gameRepository->findAll();
        foreach ($games as $game) {
            $io->writeln("Id: {$game->getId()}");
            $io->writeln("Status: {$game->getStatus()->value}");
            $io->writeln("Winner: {$game->getWinner()?->getEmail()}");
            $io->writeln("Player 1: {$game->getPlayer1()?->getEmail()}");
            $io->writeln("Player 2: {$game->getPlayer2()?->getEmail()}");
        }



        return Command::SUCCESS;
    }
}
