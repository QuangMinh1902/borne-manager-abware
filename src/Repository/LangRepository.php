<?php

namespace App\Repository;

use App\Entity\Lang;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lang|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lang|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lang[]    findAll()
 * @method Lang[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LangRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lang::class);
    }
    public function findAll()
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'l')
            ->add('from', 'App\Entity\Lang l')
            ->getQuery())
            ->getResult();
    }
    public function findOneById($id)
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'l')
            ->add('from', 'App\Entity\Lang l')
            ->add('where', 'l.id=' . $id)->getQuery())
            ->getResult();
    }
    public function findLangByCat($idCatalog): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DISTINCT lang.code,lang.lang,lang.idLang FROM lang 
        INNER JOIN content ON content.id_lang = lang.idLang 
        INNER JOIN catalog ON content.id_catalog = catalog.idCatalog 
        WHERE catalog.idCatalog = :idCatalog;";
        $resultSet = $conn->executeQuery($sql, ['idCatalog' => $idCatalog]);
        return $resultSet->fetchAllAssociative();
    }

    public function checkExisted($code, $lang)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT * FROM `lang` WHERE code = :code OR lang = :lang;";
        $resultSet = $conn->executeQuery($sql, ['code' => $code, 'lang' => $lang]);
        return empty($resultSet->fetchAllAssociative()) ? true : false;
    }
}
