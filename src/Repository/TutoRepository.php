<?php

namespace App\Repository;

use App\Entity\Tuto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tuto>
 */
class TutoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tuto::class);
    }

    public function findAllWithChaptersAndContent()
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.chapters', 'c')
            ->leftJoin('c.contents', 'cnt')
            ->addSelect('c')
            ->addSelect('cnt')
            ->getQuery()
            ->getResult();
    }

    public function findCustom($id): ?Tuto {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.chapters', 'c')
            ->leftJoin('c.contents', 'con')
            ->addSelect('c')
            ->addSelect('con')
            ->andWhere('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    

    //    /**
    //     * @return Tuto[] Returns an array of Tuto objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tuto
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
