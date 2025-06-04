<?php


namespace App\Repository;

use App\Entity\CartLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartLog>
 */
class CartLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartLog::class);
    }

    public function findRecentLogs(int $limit = 100): array
    {
        return $this->createQueryBuilder('cl')
            ->orderBy('cl.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByLevel(string $level): array
    {
        return $this->createQueryBuilder('cl')
            ->andWhere('cl.level = :level')
            ->setParameter('level', $level)
            ->orderBy('cl.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(string $userIdentifier): array
    {
        return $this->createQueryBuilder('cl')
            ->andWhere('cl.userIdentifier = :userIdentifier')
            ->setParameter('userIdentifier', $userIdentifier)
            ->orderBy('cl.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProduct(int $productId): array
    {
        return $this->createQueryBuilder('cl')
            ->andWhere('cl.productId = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('cl.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('cl');
        
        $stats = $qb
            ->select('cl.level, COUNT(cl.id) as count')
            ->groupBy('cl.level')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($stats as $stat) {
            $result[$stat['level']] = $stat['count'];
        }

        return $result;
    }
}