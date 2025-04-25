<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\GameStatus;
use App\Repository\GameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(enumType: GameStatus::class)]
    private ?GameStatus $status = null;

    #[ORM\ManyToOne]
    private ?User $player1 = null;

    #[ORM\ManyToOne]
    private ?User $player2 = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $playersReady = [];

    #[ORM\ManyToOne]
    private ?User $winner = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $currentTurn = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = GameStatus::WAITING_FOR_ANOTHER_PLAYER;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): ?GameStatus
    {
        return $this->status;
    }

    public function setStatus(GameStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPlayer1(): ?User
    {
        return $this->player1;
    }

    public function setPlayer1(?User $player1): static
    {
        $this->player1 = $player1;

        return $this;
    }

    public function getPlayer2(): ?User
    {
        return $this->player2;
    }

    public function setPlayer2(?User $player2): static
    {
        $this->player2 = $player2;

        return $this;
    }

    public function getPlayersReady(): array
    {
        return $this->playersReady;
    }

    public function addPlayerReady(?Uuid $uuid): static
    {
        $this->playersReady[] = $uuid;
        return $this;
    }

    public function removePlayerReady(?Uuid $uuid): static
    {
        unset($this->playersReady[$uuid->toString()]);
        return $this;
    }

    public function isPlayerReady(?Uuid $uuid): bool
    {
        return in_array($uuid->toString(), $this->playersReady);
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getCurrentTurn(): ?User
    {
        return $this->currentTurn;
    }

    public function setCurrentTurn(?User $currentTurn): static
    {
        $this->currentTurn = $currentTurn;

        return $this;
    }
}
