<?php

namespace App\Controller;

use App\Entity\Catalog;
use App\Entity\Category;
use App\Entity\Content;
use App\Entity\Media;
use App\Entity\Lang;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CatalogRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class EventController extends AbstractController
{
    public function __construct(
        public CatalogRepository $catalogRepository,
        public EntityManagerInterface $entityManager,
        public SerializerInterface $serialize
    ) {
        $this->entityManager = $entityManager;
        $this->catalogRepository = $catalogRepository;
        $this->serialize = $serialize;
    }

    // Show page to create new Event
    #[Route('/event', name: 'event')]
    public function index(EntityManagerInterface $manager)
    {
        $category = $manager->getRepository(Category::class)->findAll();
        if (!$category) throw $this->createNotFoundException('No Category found');

        $lang = $manager->getRepository(Lang::class)->findAll();
        if (!$lang) throw $this->createNotFoundException('No lang found');

        return $this->render('event/create/index.html.twig', [
            'controller_title' => 'Create Event',
            'categories' => $category,
            'langs' => $lang,
            'clist' => [],
        ]);
    }

    // Get/submit Form to create new event 
    #[Route('/event/create', name: 'event_create')]
    public function create(Request $request, EntityManagerInterface $manager)
    {
        $catalog = new Catalog();

        $catalog->setStartDate($request->request->get('startDate'));
        $catalog->setEndDate($request->request->get('endDate'));

        $catalog->setIdCategory(
            ($request->request->get('idCategory') != "Selectionner") ? (int)$request->request->get('idCategory') : 0
        );

        $this->entityManager->persist($catalog);
        $this->entityManager->flush();

        $idCatalog = $manager->getRepository(Catalog::class)->getLastId();

        $contentDB = ['event', 'information', 'description', 'title', 'subtitle'];
        foreach ($contentDB as $contDB) {
            $content = new Content();
            $content->setIdCatalog($idCatalog);
            $content->setContent($request->request->get($contDB));
            $content->setType($contDB);
            $content->setIdLang($request->request->get('idLang') ? (int)$request->request->get('idLang') : 1);

            $this->entityManager->persist($content);
            $this->entityManager->flush();
        }

        $directory = $this->getParameter('kernel.project_dir') . '/public/uploads/';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
            // mkdir($this->getParameter('kernel.project_dir') . '/public/uploads/' .  (int)$idCatalog, 0777); 
        }

        $mediaDB = ['logo', 'qrcode', 'cover', 'vis1', 'vis2', 'video'];
        foreach ($mediaDB as $mDB) {
            $array = explode('.', $_FILES[$mDB]['name']);
            $extension = end($array);

            switch ($mDB) {
                case "logo":
                    $filename = $idCatalog . '_IL.' . $extension;
                    break;
                case "qrcode":
                    $filename = $idCatalog . '_I2.' . $extension;
                    break;
                case "cover":
                    $filename = $idCatalog . '_IJ.' . $extension;
                    break;
                case "vis1":
                    $filename = $idCatalog . '_II.' . $extension;
                    break;
                case "vis2":
                    $filename = $idCatalog . '_IE.' . $extension;
                    break;
                case "video":
                    $filename = $idCatalog . '_V.' . $extension;
                    break;
                default:
                    $filename = $_FILES[$mDB]['name'];
                    break;
            }

            $media = new Media();
            $media->setIdCatalog($idCatalog);
            $media->setMediaSpace($mDB);
            $media->setName($filename ? $filename : "");

            $this->entityManager->persist($media);
            $this->entityManager->flush();
            $idMedia = $manager->getRepository(Media::class)->getLastId();

            if ($_FILES[$mDB]["tmp_name"] && $_FILES[$mDB]["name"])
                $logo = move_uploaded_file($_FILES[$mDB]["tmp_name"], $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename);


            $m = $manager->getRepository(Media::class)->findOneById((int)$idMedia);
            if (!isset($m[0])) throw $this->createNotFoundException('No media found for id ' . $idMedia);
            $m = $m[0];
            $m->setFilePath('/uploads/' . $filename);

            $manager->persist($m);
            $manager->flush();
        }

        if ($idCatalog) $this->addFlash('success', 'event Created! Knowledge is power!');

        return $this->redirectToRoute('edit_event', [
            'idCatalog' => $idCatalog ? (int)$idCatalog : '',
            'idLang' => $request->request->get('idLang') ? (int)$request->request->get('idLang') : 1,
        ]);
    }

    // show event form to create new language by catalog
    #[Route('/event/new/{idCatalog}', name: 'new_event')]
    public function new(int $idCatalog, EntityManagerInterface $manager)
    {
        $catalog = $manager->getRepository(Catalog::class)->getOneCatalog((int)$idCatalog);
        // $catalog = $this->getDoctrine()->getRepository(Catalog::class)->getEvent((int)$idCatalog, (int)$idLang);
        if (!$catalog) throw $this->createNotFoundException('No Catalog found');

        $getAllLang = $manager->getRepository(Content::class)->getAllLangByCatalog((int)$idCatalog);
        if (!$getAllLang) throw $this->createNotFoundException('No Content Lang found');

        $category = $manager->getRepository(Category::class)->findAll();
        if (!$category) throw $this->createNotFoundException('No Category found');

        $lang = $manager->getRepository(Lang::class)->findAll();
        if (!$lang) throw $this->createNotFoundException('No lang found');

        return $this->render('event/show/index.html.twig', [
            'controller_title' => 'Create Event',
            'categories' => $category,
            'langs' => $lang,
            'idCatalog' => (int)$idCatalog,
            'langSelect' => $getAllLang,
            'clist' => $catalog,
        ]);
    }

    // Get/submit Form to create new event 
    #[Route('/event/lang/{idCatalog}', name: 'new_event_lang')]
    public function newLang(int $idCatalog, Request $request, EntityManagerInterface $manager)
    {
        $catalog = $manager->getRepository(Catalog::class)->findOneById((int)$idCatalog)[0];
        $catalog->setStartDate($request->request->get('startDate'));
        $catalog->setEndDate($request->request->get('endDate'));
        $catalog->setIdCategory(($request->request->get('idCategory') != "Selectionner") ?: (int)$request->request->get('idCategory'));

        $manager->persist($catalog);
        $manager->flush();

        $contentDB = ['event', 'information', 'description', 'title', 'subtitle'];
        foreach ($contentDB as $contDB) {
            $content = new Content();
            $content->setIdCatalog($idCatalog);
            $content->setContent($request->request->get($contDB));
            $content->setType($contDB);
            $content->setIdLang($request->request->get('idLang') ?
                (int)$request->request->get('idLang') : 0);

            $this->entityManager->persist($content);
            $this->entityManager->flush();
        }

        $mediaDB = ['logo', 'qrcode', 'cover', 'vis1', 'video', 'vis2'];
        foreach ($mediaDB as $mDB) {
            if (!empty($_FILES[$mDB]['name'])) {
                $array = explode('.', $_FILES[$mDB]['name']);
                $extension = end($array);

                switch ($mDB) {
                    case "logo":
                        $filename = $idCatalog . '_IL.' . $extension;
                        break;
                    case "qrcode":
                        $filename = $idCatalog . '_I2.' . $extension;
                        break;
                    case "cover":
                        $filename = $idCatalog . '_IJ.' . $extension;
                        break;
                    case "vis1":
                        $filename = $idCatalog . '_II.' . $extension;
                        break;
                    case "vis2":
                        $filename = $idCatalog . '_IE.' . $extension;
                        break;
                    case "video":
                        $filename = $idCatalog . '_V.' . $extension;
                        break;
                    default:
                        $filename = $_FILES[$mDB]['name'];
                        break;
                }
            } else {
                $filename = $request->request->get($mDB) ? $request->request->get($mDB) : "";
            }

            if ($res = $manager->getRepository(Media::class)->findOneByIdCatalog((int)$idCatalog, $mDB)) {
                $media = $res[0];
                if (isset($idCatalog) && $idCatalog) $media->setIdCatalog($idCatalog);
                if (isset($mDB) && $mDB) $media->setMediaSpace($mDB);
                if (isset($filename) && $filename) $media->setName($filename ?: "");
                $media->setFilePath('/uploads/' . $filename);

                $manager->persist($media);
                $manager->flush();

                if ($_FILES[$mDB]["tmp_name"] && $_FILES[$mDB]["name"])
                    $logo = move_uploaded_file($_FILES[$mDB]["tmp_name"], $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename);
            }
        }

        if ($idCatalog) $this->addFlash('success', 'event Update! Knowledge is power!');

        return $this->redirectToRoute('edit_event', [
            'idCatalog' => $idCatalog ? (int)$idCatalog : '',
            'idLang' => $request->request->get('idLang') ? (int)$request->request->get('idLang') : 1,
        ]);
    }

    // Edit form to update event
    #[Route('/event/edit/{idCatalog}/{idLang}', name: 'edit_event')]
    public function edit($idCatalog, $idLang, EntityManagerInterface $manager)
    {
        $category = $manager->getRepository(Category::class)->findAll();
        if (!isset($category) || empty($category)) {
            throw $this->createNotFoundException('No Category found');
        }

        $getAllLang = $manager->getRepository(Content::class)->getAllLangByCatalog((int)$idCatalog);
        if (!isset($getAllLang) || empty($getAllLang)) {
            throw $this->createNotFoundException('there is any lang');
        }

        $lang = $manager->getRepository(Lang::class)->findAll();
        if (!isset($lang) || empty($lang)) {
            throw $this->createNotFoundException('No lang found');
        }

        $langNotRegistered = $manager->getRepository(Content::class)->getAllLangNotRegistered($idCatalog);

        $ctList = $manager->getRepository(Catalog::class)->getEvent($idCatalog, $idLang);

        return $this->render('event/update/index.html.twig', [
            'controller_title' => 'Edit Event',
            'idCatalog' => (int)$idCatalog,
            'idLang' => (int)$idLang,
            'clist' => $ctList,
            'categories' => $category,
            'langs' => $lang,
            'langSelect' => $getAllLang,
            'langNotRegistered' => $langNotRegistered
        ]);
    }

    #[Route('/event/view/{idCatalog}/{idLang}', name: 'catalog_view')]
    public function view(EntityManagerInterface $manager, $idCatalog, $idLang)
    {
        $category = $manager->getRepository(Category::class)->findAll();
        
        if (!isset($category) || empty($category)) {
            throw $this->createNotFoundException('No Category found');
        }

        $langs = $manager->getRepository(Lang::class)->findLangByCat($idCatalog);
        $ctList = $manager->getRepository(Catalog::class)->getEvent($idCatalog, $idLang);
        return $this->render('event/view/catalog.html.twig', [
            'controller_title' => 'Edit Event',
            'idCatalog' => (int)$idCatalog,
            'idLang' => (int)$idLang,
            'clist' => $ctList,
            'categories' => $category,
            'langs' => $langs
        ]);
    }

    #[Route('/event/update/{idCatalog}/{idLang}', name: 'update_event')]
    public function UPDATE(int $idCatalog, int $idLang, Request $request, EntityManagerInterface $manager)
    {
        $catalog = $manager->getRepository(Catalog::class)->findOneById((int)$idCatalog)[0];

        $catalog->setStartDate($request->request->get('startDate'));
        $catalog->setEndDate($request->request->get('endDate'));
        $catalog->setIdCategory(($request->request->get('idCategory') != "Selectionner") ? (int)$request->request->get('idCategory') : 0);

        $manager->persist($catalog);
        $manager->flush();

        $contentDB = ['event', 'information', 'description', 'title', 'subtitle'];
        foreach ($contentDB as $contDB) {
            $content = $manager->getRepository(Content::class)->findOneByIdCatalog((int)$idCatalog, (int)$idLang, $contDB)[0];

            $content->setIdCatalog($idCatalog);
            $content->setIdLang($idLang ? $idLang : (int)$request->request->get('idLang'));
            $content->setContent($request->request->get($contDB));
            $content->setType($contDB);

            $manager->persist($content);
            $manager->flush();
        }

        $mediaDB = ['logo', 'qrcode', 'cover', 'vis1', 'video', 'vis2'];
        foreach ($mediaDB as $mDB) {
            if (!empty($_FILES[$mDB]['name'])) {

                $array = explode('.', $_FILES[$mDB]['name']);
                $extension = end($array);

                switch ($mDB) {
                    case "logo":
                        $filename = $idCatalog . '_IL.' . $extension;
                        break;
                    case "qrcode":
                        $filename = $idCatalog . '_I2.' . $extension;
                        break;
                    case "cover":
                        $filename = $idCatalog . '_IJ.' . $extension;
                        break;
                    case "vis1":
                        $filename = $idCatalog . '_II.' . $extension;
                        break;
                    case "vis2":
                        $filename = $idCatalog . '_IE.' . $extension;
                        break;
                    case "video":
                        $filename = $idCatalog . '_V.' . $extension;
                        break;
                    default:
                        $filename = $_FILES[$mDB]['name'];
                        break;
                }
            } else {
                $filename = $request->request->get($mDB) ? $request->request->get($mDB) : "";
            }

            if ($res = $manager->getRepository(Media::class)->findOneByIdCatalog((int)$idCatalog, $mDB)) {
                $media = $res[0];
                $media->setIdCatalog($idCatalog);
                $media->setMediaSpace($mDB);
                $media->setName($filename ?: "");
                $media->setFilePath('/uploads/' . $filename);

                $manager->persist($media);
                $manager->flush();

                if ($_FILES[$mDB]["tmp_name"] && $_FILES[$mDB]["name"])
                    $logo = move_uploaded_file($_FILES[$mDB]["tmp_name"], $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename);
            }
        }

        if ($idCatalog) $this->addFlash('success', 'event Update! Knowledge is power!');

        return $this->redirectToRoute('catalog_lang', [
            // 'idCatalog' => $idCatalog ? (int)$idCatalog : '', 
            'idLang' => $idLang ? (int)$idLang :  (int)$request->request->get('idLang'),
        ]);
    }

    #[Route('/event/clear', name: 'flash_clear', methods: ["POST"])]
    function clear(Request $request)
    {
        $jsons = $request->request->get('flashType');
        $json = json_decode($jsons, true);

        return new Response(
            $this->serialize->serialize($json, 'json'),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}
