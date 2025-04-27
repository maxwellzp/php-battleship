<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\GameService;
use App\Service\MercureService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'game:create-game',
    description: 'Add a short description for your command',
)]
class GameCreateGameCommand extends Command
{
    public function __construct(
        private GameService $gameService,
        private MercureService $mercureService,
        private UserRepository $userRepository,
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

        $player = $this->userRepository->findOneBy(['email' => 'player1@example.com']);
        $game = $this->gameService->createNewGame($player);
        $io->writeln(sprintf("GAME ID: %s", $game->getId()));
        $this->mercureService->publishNewGame($game, 'url');

        $player = $this->userRepository->findOneBy(['email' => 'player2@example.com']);
        $game = $this->gameService->joinGame($game, $player);
        $io->writeln(sprintf("NEXT TURN: %s", $game->getCurrentTurn()->getEmail()));
        $this->mercureService->publishJoinedGame($game, 'url');

        return Command::SUCCESS;
    }
}
