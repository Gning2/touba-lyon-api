<?php

namespace App\Repository;

use App\Entity\Loan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function findWithFilters(?string $status = null, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.items', 'i')
            ->leftJoin('i.material', 'm')
            ->addSelect('i', 'm')
            ->orderBy('l.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('l.status = :status')->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('l.borrowerName LIKE :s OR l.borrowerOrganization LIKE :s')
               ->setParameter('s', "%{$search}%");
        }

        return $qb->getQuery()->getResult();
    }

    public function countByStatus(string $status): int
    {
        return (int)$this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOverdue(): int
    {
        return (int)$this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.status = :status')
            ->andWhere('l.expectedReturnDate < :now')
            ->setParameter('status', 'active')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTopBorrowers(int $limit = 5): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.borrowerName, l.borrowerOrganization, COUNT(l.id) as loanCount')
            ->groupBy('l.borrowerName, l.borrowerOrganization')
            ->orderBy('loanCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }
}
