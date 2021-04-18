<?php

namespace App\Repository;

use App\Entity\Exam;
use App\Entity\Result;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Result|null find($id, $lockMode = null, $lockVersion = null)
 * @method Result|null findOneBy(array $criteria, array $orderBy = null)
 * @method Result[]    findAll()
 * @method Result[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Result::class);
    }

    public function findClosedResults(User $user = null) {
        if ($user === null) {
            return $this->createQueryBuilder('r')
                ->Where('r.status != :status')
                ->setParameter('status', Result::STATUS_OPEN)
                ->getQuery()
                ->getResult()
                ;
        }

        $qb = $this->createQueryBuilder('r');

        return $qb->where('r.user = :user')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('r.status', ':status'),
                $qb->expr()->eq('r.status', ':status_marked')
            ))
        ->setParameter('user', $user)
        ->setParameter('status', Result::STATUS_CLOSE)
        ->setParameter('status_marked', Result::STATUS_CLOSE_MARKED)
        ->getQuery()
        ->getResult()
        ;

    }

    public function findCurrentResult(User $user, Exam $exam)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.exam = :exam')
            ->setParameter('user', $user)
            ->setParameter('exam', $exam)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Result[] Returns an array of Result objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Result
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
