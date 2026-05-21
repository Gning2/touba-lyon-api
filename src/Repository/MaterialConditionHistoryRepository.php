<?php

namespace App\Repository;

use App\Entity\MaterialConditionHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaterialConditionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterialConditionHistory::class);
    }

    public function findByMaterial(int $materialId, int $limit = 50): array
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.condition', 'c')
            ->addSelect('c')
            ->where('h.material = :materialId')
            ->setParameter('materialId', $materialId)
            ->orderBy('h.changedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
