<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ShotResult;
use App\Repository\ShotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ShotRepository::class)]
class Shot
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'shots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Board $board = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $player = null;

    #[ORM\Column]
    private ?int $x = null;

    #[ORM\Column]
    private ?int $y = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $firedAt = null;

    #[ORM\Column(type: 'string', enumType: ShotResult::class)]
    private ShotResult $result;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): static
    {
        $this->board = $board;

        return $this;
    }

    public function getPlayer(): ?User
    {
        return $this->player;
    }

    public function setPlayer(?User $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(int $x): static
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function setY(int $y): static
    {
        $this->y = $y;

        return $this;
    }

    public function getFiredAt(): ?\DateTimeImmutable
    {
        return $this->firedAt;
    }

    public function setFiredAt(\DateTimeImmutable $firedAt): static
    {
        $this->firedAt = $firedAt;

        return $this;
    }

    public function markAsHit(): void
    {
        $this->result = ShotResult::HIT;
    }

    public function markAsMiss(): void
    {
        $this->result = ShotResult::MISS;
    }

    public function markAsSunk(): void
    {
        $this->result = ShotResult::SUNK;
    }

    public function getResult(): ShotResult
    {
        return $this->result;
    }

    public function setResult(ShotResult $result): void
    {
        $this->result = $result;
    }
}
