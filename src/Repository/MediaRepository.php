<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }
    public function findAll()
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'm')
            ->add('from', 'App\Entity\Media m')
            ->getQuery())
            ->getResult();
    }
    public function findOneById($id)
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'm')
            ->add('from', 'App\Entity\Media m')
            ->add('where', 'm.id=' . $id)->getQuery())
            ->getResult();
    }
    public function getLastId()
    {
        return  (int)($this
            ->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'max(m.id) as idMedia')
            ->add('from', 'App\Entity\Media m')
            ->getQuery()
            ->getResult())[0]['idMedia'];
    }
    
    public function findAllByIdCatalog($id)
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'm')
            ->add('from', 'App\Entity\Media m')
            ->add('where', 'm.idCatalog=' . $id)->getQuery())
            ->getResult();
    }
    
    public function findOneByIdCatalog($idCatalog, $mediaspace)
    {  
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'm')
            ->add('from', 'App\Entity\Media m')
            ->add('where', 'm.idCatalog=' . $idCatalog)
            ->andWhere("m.mediaspace='$mediaspace'") 
            ->getQuery())
            ->getResult(); 
       
    }
 
}
