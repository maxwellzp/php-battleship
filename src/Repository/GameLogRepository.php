<?php

namespace App\Repository;

use App\Entity\GameLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameLog>
 */
class GameLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameLog::class);
    }

    public function save(GameLog $gameLog, bool $flush = false): void
    {
        $this->getEntityManager()->persist($gameLog);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
