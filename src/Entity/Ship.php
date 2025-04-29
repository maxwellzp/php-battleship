<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ShipOrientation;
use App\Enum\ShipType;
use App\Enum\ShotResult;
use App\Repository\ShipRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ShipRepository::class)]
class Ship
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(enumType: ShipType::class)]
    private ?ShipType $type = null;

    #[ORM\Column]
    private ?int $size = null;

    #[ORM\Column(enumType: ShipOrientation::class)]
    private ?ShipOrientation $orientation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Board $board = null;

    #[ORM\Column(type: 'json')]
    private array $coordinates = [];

    #[ORM\Column]
    private ?bool $isSunk = null;

    public function __construct(ShipType $type)
    {
        $this->type = $type;
        $this->size = $type->getSize();
        $this->isSunk = false;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getType(): ?ShipType
    {
        return $this->type;
    }

    public function setType(ShipType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getOrientation(): ?ShipOrientation
    {
        return $this->orientation;
    }

    public function setOrientation(ShipOrientation $orientation): static
    {
        $this->orientation = $orientation;

        return $this;
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

    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    public function setCoordinates(array $coordinates): static
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function isSunk(): ?bool
    {
        return $this->isSunk;
    }

    public function setIsSunk(bool $isSunk): static
    {
        $this->isSunk = $isSunk;

        return $this;
    }


    public function isSunkByShots(Collection $shots): bool
    {
        foreach ($this->coordinates as $coord) {
            $hitAtThisCoord = false;

            foreach ($shots as $shot) {
                if (
                    $shot->getX() === $coord['x'] &&
                    $shot->getY() === $coord['y'] &&
                    $shot->getResult() === ShotResult::HIT
                ) {
                    $hitAtThisCoord = true;
                    break;
                }
            }

            if (!$hitAtThisCoord) {
                return false;
            }
        }

        return true;
    }
}
