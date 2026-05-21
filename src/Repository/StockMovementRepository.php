<?php

namespace App\Repository;

use App\Entity\StockMovement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockMovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockMovement::class);
    }

    public function findWithFilters(
        ?int $materialId,
        ?string $type,
        ?\DateTime $dateFrom,
        ?\DateTime $dateTo,
        int $page = 1,
        int $limit = 50
    ): array {
        $qb = $this->createQueryBuilder('sm')
            ->innerJoin('sm.material', 'm')
            ->leftJoin('m.category', 'c')
            ->addSelect('m', 'c')
            ->orderBy('sm.createdAt', 'DESC');

        if ($materialId) {
            $qb->andWhere('m.id = :mid')->setParameter('mid', $materialId);
        }
        if ($type) {
            $qb->andWhere('sm.type = :type')->setParameter('type', $type);
        }
        if ($dateFrom) {
            $qb->andWhere('sm.createdAt >= :from')->setParameter('from', $dateFrom);
        }
        if ($dateTo) {
            $dateTo->setTime(23, 59, 59);
            $qb->andWhere('sm.createdAt <= :to')->setParameter('to', $dateTo);
        }

        $countQb = clone $qb;
        $total = (int)$countQb->select('COUNT(sm.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult(($page - 1) * $limit)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();

        return ['items' => $items, 'total' => $total];
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('sm')
            ->innerJoin('sm.material', 'm')
            ->addSelect('m')
            ->orderBy('sm.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getGlobalStats(): array
    {
        return $this->createQueryBuilder('sm')
            ->select('sm.type, COUNT(sm.id) as count, SUM(sm.quantity) as totalQty')
            ->groupBy('sm.type')
            ->getQuery()
            ->getArrayResult();
    }
}
