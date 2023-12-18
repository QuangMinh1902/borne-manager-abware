<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\Media;
use App\Entity\Catalog;
use App\Entity\Content;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CatalogController extends AbstractController
{
   #[Route('/', name: 'catalog')]
   public function index(): Response
   {
      return $this->redirectToRoute('catalog_lang', [
         'idLang' => '1'
      ]);
   }

   #[Route('/catalog/lang/{idLang}', name: 'catalog_lang')]
   public function catalogByLang(int $idLang, PaginatorInterface $paginator, EntityManagerInterface $manager, Request $request): Response
   {
      // Get catalog list
      $catalog = $paginator->paginate(
         $manager->getRepository(Catalog::class)->getAllByLang($idLang),
         $request->query->getInt('page', 1),
         10
      );

      if (!isset($catalog) || empty($catalog)) {
         throw $this->createNotFoundException("No Catalog found");
      }

      // Get category list
      $category = $manager->getRepository(Category::class)->findAll();
      if (!isset($category) || empty($category)) {
         throw $this->createNotFoundException("No Category found");
      }

      // get langague list
      $langs  = $manager->getRepository(Lang::class)->findAll();
      if (!isset($langs) || empty($langs)) {
         throw $this->createNotFoundException("No lang found");
      }

      return $this->render('catalog/index.html.twig', [
         'controller_name' => "Gestion du catalogue de vidéos",
         'controller_title' => 'Catalogue Liste',
         'catalogs' => $catalog,
         'categories' => $category,
         'langs' => $langs,
         'idLang' => $idLang
      ]);
   }

   #[Route('/catalog/search/{idLang}', name: 'catalog_search')]
   public function search($idLang, Request $request, EntityManagerInterface $manager, PaginatorInterface $paginator): Response
   {
      $title = $request->request->get('search');
      $catalog = $paginator->paginate(
         $manager->getRepository(Catalog::class)->search($idLang, $title),
         $request->query->getInt('page', 1),
         10
      );
      if (!isset($catalog) && empty($catalog)) {
         $this->addFlash('warning', 'No title found for the search catalog for ' . $title);
         return $this->redirectToRoute('catalog_lang', [
            'idLang' => $idLang
         ]);
      }

      $category = $manager->getRepository(Category::class)->findAll();
      if (!(isset($category) || empty($category))) {
         throw $this->createNotFoundException("No category found ");
      }
      $langs = $manager->getRepository(Lang::class)->findAll();
      if (!isset($langs) || empty($langs)) {
         throw $this->createNotFoundException("No lang found");
      }

      return $this->render("catalog/index.html.twig", [
         'controller_name' => 'Gestion du catalogue de vidéos',
         'controller_title' => 'Catalogue Liste',
         'catalogs' => $catalog,
         'categories' => $category,
         'idLang' => $idLang,
         'langs' => $langs
      ]);
   }

   #[Route('/catalog/rubric/{idRubric}/{idLang}', name: 'catalog_by_rubric')]
   public function searchByRubric($idRubric, $idLang, Request $request, EntityManagerInterface $manager, PaginatorInterface $paginator): Response
   {
      // Get catalog list
      $catalog = $paginator->paginate(
         $manager->getRepository(Catalog::class)->getAllByRubric($idRubric, $idLang),
         $request->query->getInt('page', 1),
         10
      );

      if (!isset($catalog) || empty($catalog)) {
         throw $this->createNotFoundException("No Catalog found");
      }

      // Get category list
      $category = $manager->getRepository(Category::class)->findAll();
      if (!isset($category) || empty($category)) {
         throw $this->createNotFoundException("No Category found");
      }

      // get langague list
      $langs  = $manager->getRepository(Lang::class)->findAll();
      if (!isset($langs) || empty($langs)) {
         throw $this->createNotFoundException("No lang found");
      }
      return $this->render('catalog/index.html.twig', [
         'controller_name' => "Gestion du catalogue de vidéos",
         'controller_title' => 'Catalogue Liste',
         'catalogs' => $catalog,
         'categories' => $category,
         'langs' => $langs,
         'idLang' => $idLang
      ]);
   }

   #[Route('/catalog/available/{idLang}', name: 'catalog_availabe')]
   public function findCatAvailable($idLang, Request $request, EntityManagerInterface $manager, PaginatorInterface $paginator)
   {
      // Get catalog list
      $catalog = $paginator->paginate(
         $manager->getRepository(Catalog::class)->getAllCatAvailable($idLang),
         $request->query->getInt('page', 1),
         10
      );

      if (!isset($catalog) || empty($catalog)) {
         throw $this->createNotFoundException("No Catalog found");
      }

      // Get category list
      $category = $manager->getRepository(Category::class)->findAll();
      if (!isset($category) || empty($category)) {
         throw $this->createNotFoundException("No Category found");
      }

      // get langague list
      $langs  = $manager->getRepository(Lang::class)->findAll();
      if (!isset($langs) || empty($langs)) {
         throw $this->createNotFoundException("No lang found");
      }
      return $this->render('catalog/index.html.twig', [
         'controller_name' => "Gestion du catalogue de vidéos",
         'controller_title' => 'Catalogue Liste',
         'catalogs' => $catalog,
         'categories' => $category,
         'langs' => $langs,
         'idLang' => $idLang
      ]);
   }

   #[
      Route('/catalog/delete/{id}', name: 'catalog_delete'),
      IsGranted("ROLE_ADMIN")
   ]
   public function delete(EntityManagerInterface $manager, Catalog $catalog, $id)
   {
      $manager->remove($catalog);

      // delete content qui sont lié à cette catalog
      $contents = $manager->getRepository(Content::class)->findAllByIdCatalog($id);
      foreach ($contents as $content) {
         $manager->remove($content);
      }

      // delete media 
      $medias = $manager->getRepository(Media::class)->findAllByIdCatalog($id);
      foreach ($medias as $media) {
         $manager->remove($media);
      }

      $manager->flush();
      return $this->redirectToRoute('catalog');
   }

   #[Route('/catalog/filter', name: 'catalog_filter')]
   public function filter(Request $request, EntityManagerInterface $manager)
   {
      $idCategory = $request->request->get('idCategory');
      $idLang = $request->request->get('idLang');

      $filter = $idLang ?: $idCategory;
      $catalog = $manager->getRepository(Catalog::class)->filterBySelect($filter);
      if (!isset($catalog) || empty($catalog)) {
         throw $this->createNotFoundException("No category or langague found for id " . $filter);
      }

      $category = $manager->getRepository(Category::class)->findAll();
      if (!isset($category) || empty($category)) {
         throw $this->createNotFoundException('No category found');
      }

      $lang = $manager->getRepository(Lang::class)->findAll();
      if (!isset($lang) || empty($lang)) {
         throw $this->createNotFoundException("No lang found");
      }

      return $this->render('catalog/index.html.twig', [
         'controller_name' => 'Gestion du catalogue de vidéos',
         'controller_title' => 'Catalogue Liste',
         'catalogs' => $catalog,
         'categories' => $category,
         'langs' => $lang
      ]);
   }

  

}
