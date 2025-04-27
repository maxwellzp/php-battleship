<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Board;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Board>
 */
class BoardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Board::class);
    }
    public function save(Board $board, bool $flush = false): void
    {
        $this->getEntityManager()->persist($board);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
