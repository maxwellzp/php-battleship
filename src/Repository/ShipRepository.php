<?php

namespace App\Repository;

use App\Entity\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ship>
 */
class ShipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ship::class);
    }

    public function save(Ship $ship, bool $flush = false): void
    {
        $this->getEntityManager()->persist($ship);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
