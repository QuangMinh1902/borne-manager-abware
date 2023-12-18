<?php
namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Category[]    extractCategoryAndChild
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findAll()
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c')
            ->add('from', 'App\Entity\Category c')
            ->getQuery())
            ->getResult();
    }
    public function findOneById($id)
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c')
            ->add('from', 'App\Entity\Category c')
            ->add('where', 'c.id=' . $id)->getQuery())
            ->getResult();
    }

    public function extractCategoryAndChild(){

         $query = $this->getEntityManager()
             ->createQueryBuilder()
             ->add('select', 'cat.id as idCategory, scat.id as idSubCategory,  cat.category,cat.categoryEng,cat.categoryEsp,cat.categoryAll,cat.picture,scat.subCategory,scat.subCategoryEng,scat.subCategoryEsp,scat.subCategoryAll,csc.idCatalog')
             ->from( 'App\Entity\category', 'cat')
             ->leftjoin( 'App\Entity\SubCategory', 'scat', 'WITH', 'cat.id = scat.idCategory')
             ->leftjoin( 'App\Entity\CatalogSubCategory', 'csc', 'WITH', 'scat.id = csc.idSubCategory')
             //->where('cat.id = scat.idCategory')
             //->where('scat.id = csc.idSubCategory')
             ->addOrderBy('cat.id', 'ASC')
             ->addOrderBy('scat.id', 'ASC')
             ->getQuery();

             // dd($query->getSQL());
             return $query->getResult();
 }

}

