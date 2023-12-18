<?php

namespace App\Repository;

use App\Entity\Content;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Content|null find($id, $lockMode = null, $lockVersion = null)
 * @method Content|null findOneBy(array $criteria, array $orderBy = null)
 * @method Content[]    findAll()
 * @method Content[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    // /**
    //  * @return Content[] Returns an array of Content objects
    //  */ 
    public function findOneById($id)
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c')
            ->add('from', 'App\Entity\Content c')
            ->add('where', 'c.id=' . $id)->getQuery())
            ->getResult();
    }
    public function getLastId()
    {
        return  (int)($this
            ->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'max(c.id) as idContent')
            ->add('from', 'App\Entity\Content c')
            ->getQuery()
            ->getResult())[0]['idContent'];
    } 
    
    public function findAllByIdCatalog($id)
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c')
            ->add('from', 'App\Entity\Content c')
            ->add('where', 'c.idCatalog=' . $id)->getQuery())
            ->getResult();
    }

    public function findOneByIdCatalog($idCatalog, $idLang, $type)
    {  
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c')
            // ->add('select', 'max(c.id) as idContent')
            ->add('from', 'App\Entity\Content c')
            ->add('where', 'c.idCatalog=' . $idCatalog)
            ->andWhere("c.idLang='$idLang'") 
            ->andWhere("c.type='$type'") 
            ->getQuery())
            ->getResult(); 
       
    }

    public function getAllLangByCatalog($id)
    {  
        return ($this->getEntityManager()
            ->createQueryBuilder()
            // ->add('select', 'c.id as idCatalog, cn.id as idCatalog, lg.lang as language, lg.id as idLang, lg.code as code') 
            ->select('DISTINCT lg.lang, lg.id as idLang, lg.code as code')
            ->add('from', 'App\Entity\Catalog c, App\Entity\Content cn, App\Entity\Lang lg') 
            ->add('where', 'c.id=' . $id)
            ->andWhere('cn.idCatalog=c.id')
            ->andWhere('cn.idLang=lg.id') 
            ->getQuery())
            ->getResult();  
    }

    public function getAllLangNotRegistered($idCatalog){
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT lang.* FROM lang WHERE lang.lang NOT IN 
        (SELECT DISTINCT lang.lang FROM content INNER JOIN lang ON lang.idLang = content.id_lang 
        INNER JOIN catalog ON content.id_catalog = catalog.idCatalog where catalog.idCatalog=  :idCatalog );";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['idCatalog' => $idCatalog]);
        return $resultSet->fetchAllAssociative();
    }
}
