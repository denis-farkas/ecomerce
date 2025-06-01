<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findByVerified(): array
    {
      return $this->createQueryBuilder('c')
           ->andWhere('c.verified = :val')
           ->setParameter('val', "oui")
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
           ->getResult()
     ;
   }

   public function findRandomVerifiedComments()
    {
      $comments = $this->createQueryBuilder('c')
      ->where('c.verified = :verified')
      ->setParameter('verified', 'oui')
      ->orderBy('c.createdAt', 'DESC')
      ->getQuery()
      ->getResult();

    // Shuffle the array to randomize the order
      shuffle($comments);

    // Return the first three comments
      return array_slice($comments, 0, 3);
    }
    
//    /**
//     * @return Comment[] Returns an array of Comment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Comment
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
