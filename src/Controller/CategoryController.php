<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;  
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/category', name: 'category')]
    public function index(EntityManagerInterface $manager,Request $request): Response
    {
        $categories = $manager->getRepository(Category::class)->findAll();
        return $this->render('category/index.html.twig',[
            'categories' => $categories
        ]);
    }

    // Ces fonctionnalité ne sont pas encore utilisées 

  /*   #[Route('/category/list', name: 'category_list')]
    public function list(EntityManagerInterface $manager)
    {
        $category = $manager->getRepository(Category::class)->findAll();
        if (!isset($category) || empty($category)) {
            throw $this->createNotFoundException('No category found');
        }

        $categoryList = [
            'success' => true,
            'component' => 'Category',
            'category' => $category
        ];

        $serializedData = $this->serializer->serialize($category,'json');
        return $this->render('......')
    }

    #[Route('/category/{id}',name:'category_show')]
    public function show(EntityManagerInterface $manager,$id) 
    {
        $category = $manager->getRepository(Category::class)->findOneById($id);
        if(!isset($category) || empty($category)){
            throw $this->createNotFoundException('No category found for id : '. $id);
        }
        $serializedData = $this->serializer->serialize($category,'json');
        return $this->render('.......')
    } */ 

    #[Route('/category/create',name:'category_create',methods:['GET', 'POST'])]
    public function create(EntityManagerInterface $manager, Request $request){
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $category= $form->getData();
            $manager->persist($category);
            $manager->flush();
            $this->addFlash(
                'success',
                'New category has been created successfully'
            );
            return $this->redirectToRoute('category');
        }
        return $this->render('category/new.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    #[Route('/category/delete/{id}', name: 'category_delete')]
    public function delete(EntityManagerInterface $manager, Category $category) {
        $manager->remove($category);
        $manager->flush();
        $this->addFlash(
            'success',
            'Category has been removed successfully'
        );
        return $this->redirectToRoute('category');
    }
}
