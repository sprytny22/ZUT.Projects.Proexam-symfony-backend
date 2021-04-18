<?php

namespace App\Repository;

use App\Entity\Exam;
use App\Entity\Result;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Exam|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exam|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exam[]    findAll()
 * @method Exam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exam::class);
    }

    public function findAllWithoutArchive()
    {
        return $this->createQueryBuilder('e')
            ->where('e.status != :status')
            ->setParameter('status', Exam::STATUS_ARCHIVED)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('e')
            ->join('e.users', 'u')
            ->where('u = :user')
            ->andWhere('e.status != :status')
            ->setParameter('status', Exam::STATUS_ARCHIVED)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
            ;
    }
    // /**
    //  * @return Exam[] Returns an array of Exam objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Exam
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
