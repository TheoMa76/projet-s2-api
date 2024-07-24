<?php

namespace App\Repository;

use App\Entity\Progress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Progress>
 */
class ProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Progress::class);
    }

    public function findCustom($id): ?Progress
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.chapter', 'c')       
        ->leftJoin('c.Tuto', 't')         
        ->leftJoin('p.user', 'u')          
        ->addSelect('c')                  
        ->addSelect('t')                   
        ->addSelect('u')                   
        ->where('p.id = :id')       
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
}

    //    /**
    //     * @return Progress[] Returns an array of Progress objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Progress
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
