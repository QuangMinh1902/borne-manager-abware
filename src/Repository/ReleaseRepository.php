<?php

namespace App\Repository;

use App\Entity\Releases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Release|null find($id, $lockMode = null, $lockVersion = null)
 * @method Release|null findOneBy(array $criteria, array $orderBy = null)
 * @method Release[]    findAll()
 * @method Release[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReleaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Releases::class);
    }

    public function findAll()
    {   
            $test = $this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'r')
            ->add('from', 'App\Entity\Releases r')
            ->getQuery();

            //dd($test->getSQL());

            return $test->getResult();
    }
    public function findOneById($id)
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'r')
            ->add('from', 'App\Entity\Releases r')
            ->add('where', 'r.id=' . $id)->getQuery())
            ->getResult();
    }
}
