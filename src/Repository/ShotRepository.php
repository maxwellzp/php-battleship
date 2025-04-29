<?php

namespace App\Repository;

use App\Entity\Shot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Shot>
 */
class ShotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shot::class);
    }

    public function save(Shot $shot, bool $flush = false): void
    {
        $this->getEntityManager()->persist($shot);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
