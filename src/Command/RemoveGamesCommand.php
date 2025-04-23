<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Repository\ShipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'game:remove-all',
    description: 'Add a short description for your command',
)]
class RemoveGamesCommand extends Command
{
    public function __construct(
        private GameRepository $gameRepository,
        private EntityManagerInterface $entityManager,
        private BoardRepository $boardRepository,
        private ShipRepository $shipRepository,
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

        $ships = $this->shipRepository->findAll();
        foreach ($ships as $ship) {
            $this->entityManager->remove($ship);
        }
        $this->entityManager->flush();

        $boards = $this->boardRepository->findAll();
        foreach ($boards as $board) {
            $this->entityManager->remove($board);
        }
        $this->entityManager->flush();

        $games = $this->gameRepository->findAll();
        foreach ($games as $game) {
            $this->entityManager->remove($game);
        }
        $this->entityManager->flush();


        return Command::SUCCESS;
    }
}
