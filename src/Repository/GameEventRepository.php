<?php

namespace App\Repository;

use App\Entity\GameEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameEvent>
 */
class GameEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameEvent::class);
    }

    public function save(GameEvent $GameEvent, bool $flush = false): void
    {
        $this->getEntityManager()->persist($GameEvent);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
