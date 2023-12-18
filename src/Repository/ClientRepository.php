<?php

namespace App\Repository;

use App\Entity\Catalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalog::class);
    }

    public function details($idCatalog, $idLang)
    {
        $query = ($this->getEntityManager()
            ->createQueryBuilder()
            ->add('select', 'c.id as idCatalog, c.name as title, c.startDate as startDate, c.endDate as endDate , cg.category as category, l.lang as language, l.id as idLang')
            ->add('from', 'App\Entity\Catalog c, App\Entity\Category cg, App\Entity\Content cn, App\Entity\Lang l')
            ->add('where', 'c.id=' . $idCatalog)->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
            ->andWhere('cn.idCatalog=c.id')
            ->andWhere("cn.type='description' ")
            ->andWhere('c.idCategory=cg.id')
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
                    ->add('select', 'm.name as name')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='qrcode'")
                    ->andWhere('m.idCatalog=c.id')
                    ->getQuery())
                    ->setMaxResults(1)
                    ->getResult();
                break;
            case 'jaquette':
                $jaquette = ($this->getEntityManager()
                    ->createQueryBuilder()
                    ->add('select', 'm.name as name')
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
                    ->add('select', 'm.name as name')
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
                    ->add('select', 'm.name as name')
                    ->add('from', 'App\Entity\Catalog c,  App\Entity\Media m')
                    ->add('where', 'c.id=' . $idCatalog)->andWhere("m.mediaspace='video'")
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
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cg, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
                        ->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')
                        ->andWhere("cn.type='description' ")
                        ->andWhere('c.idCategory=cg.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();

                    break;
                case 'pratique':
                    $pratique = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as pratique')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cg, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
                        ->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')
                        ->andWhere("cn.type='pratique' ")
                        ->andWhere('c.idCategory=cg.id')
                        ->getQuery())
                        ->setMaxResults(1)
                        ->getResult();

                    break;
                case 'event':
                    $event = ($this->getEntityManager()
                        ->createQueryBuilder()
                        ->add('select', 'cn.content as event')
                        ->add('from', 'App\Entity\Catalog c, App\Entity\Category cg, App\Entity\Media m, App\Entity\Content cn, App\Entity\Lang l')
                        ->add('where', 'c.id=' . $idCatalog)->andWhere('l.id=' . $idLang)->andWhere('cn.idLang=l.id')
                        ->andWhere('cn.idCatalog=c.id')
                        ->andWhere('m.idCatalog=c.id')
                        ->andWhere("cn.type='event' ")
                        ->andWhere('c.idCategory=cg.id')
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
            $result += ["qrcode" => (isset($qrcode[0]["name"])) ? $qrcode[0]["name"] : "",];
            $result += ["logo" => (isset($logo[0]["name"])) ? $logo[0]["name"] : "",];
            $result += ["jaquette" => (isset($jaquette[0]["name"])) ? $jaquette[0]["name"] : "",];
        }

        return isset($result) ? $result : [];
    }
}
