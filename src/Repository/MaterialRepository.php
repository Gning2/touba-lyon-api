<?php

namespace App\Repository;

use App\Entity\Material;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaterialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    public function findAllWithCategory(): array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.category', 'c')
            ->addSelect('c')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(int $categoryId, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.category', 'c')
            ->addSelect('c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $categoryId);

        if ($search) {
            $qb->andWhere('m.name LIKE :search OR m.reference LIKE :search')
               ->setParameter('search', "%{$search}%");
        }

        return $qb->orderBy('m.name', 'ASC')->getQuery()->getResult();
    }

    public function search(string $term): array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.category', 'c')
            ->addSelect('c')
            ->where('m.name LIKE :term OR m.reference LIKE :term OR m.description LIKE :term')
            ->setParameter('term', "%{$term}%")
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLowStock(int $threshold = 3): array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.category', 'c')
            ->addSelect('c')
            ->where('m.availableStock <= :threshold')
            ->andWhere('m.totalStock > 0')
            ->setParameter('threshold', $threshold)
            ->orderBy('m.availableStock', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
