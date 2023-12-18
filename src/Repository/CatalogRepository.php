<?php

namespace App\Repository;

use App\Entity\Catalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Catalog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Catalog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Catalog[]    findAll()
 * @method Catalog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CatalogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalog::class);
    }

    public function findAll()
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c')
            ->add('from', 'App\Entity\Catalog c')
            ->getQuery())
            ->getResult();
    }
    public function findOneById($id)
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c')
            ->add('from', 'App\Entity\Catalog c')
            ->add('where', 'c.id=' . $id)->getQuery())
            ->getResult();
    }

    public function getLastId()
    {
        return (int)($this
            ->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'max(c.id) as idCatalog')
            ->add('from', 'App\Entity\Catalog c')
            ->getQuery()
            ->getResult())[0]['idCatalog'];
    }

    // Search requets 
    public function search($idLang, $title)
    {
        $sql = ($this
            ->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'cn.content as title, c.id as idCatalog, c.startDate as startDate, c.endDate as endDate, lg.lang as lang, lg.id as idLang, cat.category as category, cat.id as idCategory')
            ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Content cn, App\Entity\Lang lg')
            ->add('where', 'cn.content LIKE :key')
            ->andWhere('lg.id=' . $idLang)
            ->andWhere('cn.idLang=lg.id')->andWhere('cn.idCatalog=c.id')
            ->andWhere("cn.type='title' ")->andWhere('c.idCategory=cat.id')
            ->setParameter('key', '%' . $title . '%')->getQuery())
            ->getResult();

        return $sql;
    }

    // Get Catalog List + Category + Content
    public function getAllByLang($idLang)
    {
        $type = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'cn.type as type')
            ->add('from', 'App\Entity\Content cn, App\Entity\Lang l')
            ->add('where', 'l.id=' . $idLang)->andWhere('cn.idLang=l.id')
            ->getQuery())->getResult();

        if ($type) foreach ($type as $aType) {
            switch ($aType["type"]) {
                case 'title':
                    $title = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as title, c.id as idCatalog, c.startDate as startDate, c.endDate as endDate, lg.lang as lang, lg.id as idLang, cat.category as category, cat.id as idCategory')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Content cn, App\Entity\Lang lg')
                        ->add('where', 'lg.id=' . $idLang)
                        ->andWhere('cn.idLang=lg.id')->andWhere('cn.idCatalog=c.id')
                        ->andWhere("cn.type='title' ")->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->getResult();
                    break;
                default:
                    break;
            }
        }

        return isset($title) ? $title : [];
    }

    public function getAllByRubric($idRubric, $idLang): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DISTINCT catalog.idCatalog, content.content AS title, category.category,catalog.start_date as startDate,catalog.end_date as endDate, content.id_lang as idLang FROM content 
        INNER JOIN lang ON lang.idLang = content.id_lang 
        INNER JOIN catalog ON content.id_catalog = catalog.idCatalog 
        INNER JOIN category ON category.idCategory = catalog.id_category 
        WHERE category.idCategory = :idRubric AND lang.idLang =:idLang AND content.type ='title';";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['idRubric' => $idRubric, 'idLang' => $idLang]);
        return $resultSet->fetchAllAssociative();
    }

    public function getAllCatAvailable($idLang)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DISTINCT catalog.idCatalog, content.content AS title, category.category,catalog.start_date as startDate,catalog.end_date as endDate, content.id_lang as idLang FROM content 
        INNER JOIN lang ON lang.idLang = content.id_lang 
        INNER JOIN catalog ON content.id_catalog = catalog.idCatalog 
        INNER JOIN category ON category.idCategory = catalog.id_category 
        WHERE DATE_FORMAT(NOW(), '%Y-%m-%d') < end_date 
            AND lang.idLang = :idLang 
            AND content.type ='title';";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['idLang' => $idLang]);
        return $resultSet->fetchAllAssociative();
    }

    // Get Catalog List + Category
    public function findAllList()
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c.id as idCatalog, c.title as title, c.subTitle as subTitle, c.startDate as startDate, c.endDate as endDate, cat.category as category, cat.id as idCategory, lg.lang as lang, lg.id as idLang')
            ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Lang lg')
            ->andWhere('c.idCategory=cat.id')
            ->andWhere('c.idLang=lg.id')
            ->getQuery())
            ->getResult();
    }

    public function filterBySelect($idLang)
    {
        $sql = ($this
            ->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c.id as idCatalog, c.title as title, c.subTitle as subTitle, c.startDate as startDate, c.endDate as endDate, cat.category as category, cat.id as idCategory, lg.code as code, lg.id as idLang')
            ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Lang lg')
            ->add('where', 'c.idLang = :key OR c.idCategory = :key ')
            ->andWhere('c.idCategory=cat.id')
            ->andWhere('c.idLang=lg.id')
            ->setParameter('key', $idLang)->getQuery())
            ->getResult();

        return $sql;
    }

    public function extractEvents()
    {
        return ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c.id as idCatalog, c.idCategory')
            ->add('from', 'App\Entity\Catalog c')
            ->add('where', 'c.startDate <= CURRENT_DATE()')
            ->andWhere('c.endDate >= CURRENT_DATE() OR c.endDate is null')
            ->getQuery())
            ->getResult();
    }

    // Get Event by catalog 
    public function getOneCatalog($idCatalog)
    {
        $query = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c.id as idCatalog, c.startDate as startDate, c.endDate as endDate, cat.category as category, cat.id as idCategory')
            ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat')
            ->add('where', 'c.id=' . $idCatalog)->andWhere('c.idCategory=cat.id')
            ->getQuery())
            ->getResult();

        ($query) ? $result = $query[0] : [];

        $media = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'm.mediaspace as image')
            ->add('from', 'App\Entity\Media m')
            ->add('where', 'm.idCatalog=' . $idCatalog)->getQuery())
            ->getResult();
        if ($media) foreach ($media as $aMedia) switch ($aMedia["image"]) {
            case 'qrcode':
                $qrcode = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='qrcode'")
                    ->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'cover':
                $cover = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='cover'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'logo':
                $logo = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='logo'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'video':
                $video = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='video'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'vis1':
                $vis1 = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='vis1'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'vis2':
                $vis2 = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='vis2'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            default:
                break;
        }

        if (isset($result) && $result) {
            $result += ["title" =>  "",];
            $result += ["subtitle" =>  "",];
            $result += ["description" =>  "",];
            $result += ["event" =>  "",];
            $result += ["information" =>  "",];
            $result += ["video" => (isset($video[0]["name"])) ? $video[0]["name"] : "",];
            $result += ["videoPath" => (isset($video[0]["path"])) ? $video[0]["path"] : "",];
            $result += ["qrcode" => (isset($qrcode[0]["name"])) ? $qrcode[0]["name"] : "",];
            $result += ["qrcodePath" => (isset($qrcode[0]["path"])) ? $qrcode[0]["path"] : "",];
            $result += ["logo" => (isset($logo[0]["name"])) ? $logo[0]["name"] : "",];
            $result += ["logoPath" => (isset($logo[0]["path"])) ? $logo[0]["path"] : "",];
            $result += ["cover" => (isset($cover[0]["name"])) ? $cover[0]["name"] : "",];
            $result += ["coverPath" => (isset($cover[0]["path"])) ? $cover[0]["path"] : "",];
            $result += ["vis1" => (isset($vis1[0]["name"])) ? $vis1[0]["name"] : "",];
            $result += ["vis1Path" => (isset($vis1[0]["path"])) ? $vis1[0]["path"] : "",];
            $result += ["vis2" => (isset($vis2[0]["name"])) ? $vis2[0]["name"] : "",];
            $result += ["vis2Path" => (isset($vis2[0]["path"])) ? $vis2[0]["path"] : "",];
        }

        return isset($result) ? $result : [];
    }

    // get Event detail by idLang & idCatalog
    public function getEvent($idCatalog, $idLang)
    {
        $query = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c.id as idCatalog, c.startDate as startDate, c.endDate as endDate, cat.category as category, l.lang as language, l.id as idLang, cat.id as idCategory')
            ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Content cn, App\Entity\Lang l')
            ->add('where', 'c.id=' . $idCatalog)
            ->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
            ->andWhere('cn.idCatalog=c.id')->andWhere('c.idCategory=cat.id')
            ->getQuery())
            ->setMaxResults(1)
            ->getResult();

        ($query) ? $result = $query[0] : [];

        $type = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'cn.type as type')
            ->add('from', 'App\Entity\Content cn, App\Entity\Lang l')
            ->add('where', 'cn.idCatalog=' . $idCatalog)
            ->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
            ->getQuery())->getResult();
        if ($type) foreach ($type as $aType) {
            switch ($aType["type"]) {
                case 'title':
                    $title = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as title')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)
                        ->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')->andWhere("cn.type='title' ")->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();
                    break;
                case 'subtitle':
                    $subtitle = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as subtitle')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)
                        ->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')->andWhere("cn.type='subtitle' ")->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();
                    break;
                case 'description':
                    $description = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as description')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)
                        ->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')->andWhere("cn.type='description' ")->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();

                    break;
                case 'information':
                    $information = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as information')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)
                        ->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')->andWhere("cn.type='information' ")->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();

                    break;
                case 'event':
                    $event = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as event')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)
                        ->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')->andWhere("cn.type='event' ")->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();
                    break;
                default:
                    break;
            }
        }


        $media = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'm.mediaspace as image')
            ->add('from', 'App\Entity\Media m')
            ->add('where', 'm.idCatalog=' . $idCatalog)->getQuery())
            ->getResult();
        if ($media) foreach ($media as $aMedia) switch ($aMedia["image"]) {
            case 'qrcode':
                $qrcode = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='qrcode'")
                    ->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'cover':
                $cover = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='cover'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'logo':
                $logo = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='logo'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'video':
                $video = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='video'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'vis1':
                $vis1 = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='vis1'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'vis2':
                $vis2 = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='vis2'")->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            default:
                break;
        }

        if (isset($result) && $result) {
            $result += ["title" => (isset($title[0]["title"])) ? $title[0]["title"] : "",];
            $result += ["subtitle" => (isset($subtitle[0]["subtitle"])) ? $subtitle[0]["subtitle"] : "",];
            $result += ["description" => (isset($description[0]["description"])) ? $description[0]["description"] : "",];
            $result += ["event" => (isset($event[0]["event"])) ? $event[0]["event"] : "",];
            $result += ["information" => (isset($information[0]["information"])) ? $information[0]["information"] : "",];
            $result += ["video" => (isset($video[0]["name"])) ? $video[0]["name"] : "",];
            $result += ["videoPath" => (isset($video[0]["path"])) ? $video[0]["path"] : "",];
            $result += ["qrcode" => (isset($qrcode[0]["name"])) ? $qrcode[0]["name"] : "",];
            $result += ["qrcodePath" => (isset($qrcode[0]["path"])) ? $qrcode[0]["path"] : "",];
            $result += ["logo" => (isset($logo[0]["name"])) ? $logo[0]["name"] : "",];
            $result += ["logoPath" => (isset($logo[0]["path"])) ? $logo[0]["path"] : "",];
            $result += ["cover" => (isset($cover[0]["name"])) ? $cover[0]["name"] : "",];
            $result += ["coverPath" => (isset($cover[0]["path"])) ? $cover[0]["path"] : "",];
            $result += ["vis1" => (isset($vis1[0]["name"])) ? $vis1[0]["name"] : "",];
            $result += ["vis1Path" => (isset($vis1[0]["path"])) ? $vis1[0]["path"] : "",];
            $result += ["vis2" => (isset($vis2[0]["name"])) ? $vis2[0]["name"] : "",];
            $result += ["vis2Path" => (isset($vis2[0]["path"])) ? $vis2[0]["path"] : "",];
        }

        return isset($result) ? $result : [];
    }

    public function details($idCatalog, $idLang)
    {
        $query = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c.id as idCatalog, c.title as title, c.subTitle as subTitle, c.startDate as startDate, c.endDate as endDate, cat.category as category, cat.id as idCategory, l.lang as language, l.id as idLang')
            ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Content cn, App\Entity\Lang l')
            ->add('where', 'c.id=' . $idCatalog)
            ->andWhere('l.id=' . $idLang)
            ->andWhere('cn.idLang=l.id')
            ->andWhere('cn.idCatalog=c.id')
            ->andWhere("cn.type='description' ")
            ->andWhere('c.idCategory=cat.id')
            ->getQuery())
            ->setMaxResults(1)
            ->getResult();

        ($query) ? $result = $query[0] : [];
        $type = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'cn.type as type')
            ->add('from', 'App\Entity\Content cn')
            ->add('where', 'cn.idCatalog=' . $idCatalog)->andWhere('cn.idLang=' . $idLang)->getQuery())
            ->getResult();

        $media = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'm.mediaspace as image')
            ->add('from', 'App\Entity\Media m')
            ->add('where', 'm.idCatalog=' . $idCatalog)->getQuery())
            ->getResult();
        if ($media) foreach ($media as $aMedia) switch ($aMedia["image"]) {
            case 'qrcode':
                $qrcode = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='qrcode'")
                    ->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'cover':
                $cover = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='jaquette'")
                    ->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'logo':
                $logo = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='logo'")
                    ->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'video':
                $video = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='video'")
                    ->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'vis1':
                $vis1 = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name, m.filePath as path')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='vis1'")
                    ->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            default:

                break;
        }
        if ($type) foreach ($type as $aType) {
            switch ($aType["type"]) {
                case 'description':
                    $description = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as description')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
                        ->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')
                        ->andWhere("cn.type='description' ")
                        ->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();

                    break;
                case 'pratique':
                    $pratique = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as pratique')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
                        ->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')
                        ->andWhere("cn.type='pratique' ")
                        ->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();

                    break;
                case 'event':
                    $event = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as event')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cat, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
                        ->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')
                        ->andWhere("cn.type='event' ")
                        ->andWhere('c.idCategory=cat.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();
                    break;
                default:

                    break;
            }
        }
        if (isset($result) && $result) {
            $result += ["description" => (isset($description[0]["description"])) ? $description[0]["description"] : "",];
            $result += ["event" => (isset($event[0]["event"])) ? $event[0]["event"] : "",];
            $result += ["pratique" => (isset($pratique[0]["pratique"])) ? $pratique[0]["pratique"] : "",];
            $result += ["video" => (isset($video[0]["name"])) ? $video[0]["name"] : "",];
            $result += ["videoPath" => (isset($video[0]["path"])) ? $video[0]["path"] : "",];
            $result += ["qrcode" => (isset($qrcode[0]["name"])) ? $qrcode[0]["name"] : "",];
            $result += ["qrcodePath" => (isset($qrcode[0]["path"])) ? $qrcode[0]["path"] : "",];
            $result += ["logo" => (isset($logo[0]["name"])) ? $logo[0]["name"] : "",];
            $result += ["logoPath" => (isset($logo[0]["path"])) ? $logo[0]["path"] : "",];
            $result += ["cover" => (isset($cover[0]["name"])) ? $cover[0]["name"] : "",];
            $result += ["coverPath" => (isset($cover[0]["path"])) ? $cover[0]["path"] : "",];
            $result += ["vis1" => (isset($vis1[0]["name"])) ? $vis1[0]["name"] : "",];
            $result += ["vis1Path" => (isset($vis1[0]["path"])) ? $vis1[0]["path"] : "",];
        }

        return isset($result) ? $result : [];
    }
}
