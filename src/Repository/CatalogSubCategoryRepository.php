<?php

namespace App\Repository;

use App\Entity\CatalogSubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CatalogSubCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CatalogSubCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CatalogSubCategory[]    findAll()
 * @method CatalogSubCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CatalogSubCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CatalogSubCategory::class);
    }

    // /**
    //  * @return CatalogSubCategory[] Returns an array of CatalogSubCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CatalogSubCategory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
