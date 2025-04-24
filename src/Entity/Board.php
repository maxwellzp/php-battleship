<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BoardRepository::class)]
class Board
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?int $width = null;

    #[ORM\Column]
    private ?int $height = null;

    /**
     * @var Collection<int, Shot>
     */
    #[ORM\OneToMany(targetEntity: Shot::class, mappedBy: 'board', orphanRemoval: true)]
    private Collection $shots;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $player = null;

    /**
     * @var Collection<int, Ship>
     */
    #[ORM\OneToMany(targetEntity: Ship::class, mappedBy: 'board', orphanRemoval: true)]
    private Collection $ships;

    public function __construct()
    {
        $this->shots = new ArrayCollection();
        $this->ships = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return Collection<int, Shot>
     */
    public function getShots(): Collection
    {
        return $this->shots;
    }

    public function addShot(Shot $shot): static
    {
        if (!$this->shots->contains($shot)) {
            $this->shots->add($shot);
            $shot->setBoard($this);
        }

        return $this;
    }

    public function removeShot(Shot $shot): static
    {
        if ($this->shots->removeElement($shot)) {
            // set the owning side to null (unless already changed)
            if ($shot->getBoard() === $this) {
                $shot->setBoard(null);
            }
        }

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

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

    /**
     * @return Collection<int, Ship>
     */
    public function getShips(): Collection
    {
        return $this->ships;
    }

    public function addShip(Ship $ship): static
    {
        if (!$this->ships->contains($ship)) {
            $this->ships->add($ship);
            $ship->setBoard($this);
        }

        return $this;
    }

    public function removeShip(Ship $ship): static
    {
        if ($this->ships->removeElement($ship)) {
            // set the owning side to null (unless already changed)
            if ($ship->getBoard() === $this) {
                $ship->setBoard(null);
            }
        }

        return $this;
    }

    public function hasShipAt(int $x, int $y): bool
    {
        foreach ($this->ships as $ship) {
            foreach ($ship->getCoordinates() as $coord) {
                if ($coord['x'] === $x && $coord['y'] === $y) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canPlaceShip(Ship $ship): bool
    {
        foreach ($ship->getCoordinates() as $coord) {
            if ($coord['x'] < 0 || $coord['x'] >= 10 || $coord['y'] < 0 || $coord['y'] >= 10) {
                return false;
            }

            if ($this->hasShipAt($coord['x'], $coord['y'])) {
                return false;
            }
        }
        return true;
    }
}
