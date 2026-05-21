<?php

namespace App\Repository;

use App\Entity\MaterialConditionStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaterialConditionStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterialConditionStock::class);
    }

    public function findByMaterial(int $materialId): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.condition', 'c')
            ->addSelect('c')
            ->where('s.material = :materialId')
            ->setParameter('materialId', $materialId)
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
