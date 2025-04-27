<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Enum\GameStatus;
use App\Repository\BoardRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\GameStateEvaluator;
use App\Service\ShotProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'game:shooting',
    description: 'Add a short description for your command',
)]
class GameShootingCommand extends Command
{
    public function __construct(
        private ShotProcessor      $shotProcessor,
        private UserRepository     $userRepository,
        private BoardRepository    $boardRepository,
        private GameRepository     $gameRepository,
        private GameStateEvaluator $gameStateEvaluator
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $game = $this->gameRepository->find('0196770e-078f-7f33-bf9f-a02e41992a1f');
        $player1 = $this->userRepository->findOneBy(['email' => 'player1@example.com']);
        $player2 = $this->userRepository->findOneBy(['email' => 'player2@example.com']);
        $board = $this->boardRepository->findOneBy([
            'game' => $game,
            'player' => $player1,
        ]);

        $x = 2;
        $y = 4;

        try {
            $this->shotProcessor->processShot($board, $player2, $x, $y);
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }

        if ($this->gameStateEvaluator->isGameOver($game)) {
            $winner = $this->gameStateEvaluator->getWinner($game);
            if ($winner instanceof User) {
                $game->setWinner($winner);
                $game->setStatus(GameStatus::GAME_FINISHED);
                $game->setFinishedAt(new \DateTimeImmutable());
                $this->gameRepository->save($game);
            }
        }

        return Command::SUCCESS;
    }
}
