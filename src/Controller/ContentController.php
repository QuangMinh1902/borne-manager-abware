<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
 
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Content;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class ContentController extends AbstractController
{
    private $serializer;
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    
    #[Route('/content/list', name: 'content_list')]
    public function list(EntityManagerInterface $manager)
    {
        $contents = $manager->getRepository(Content::class)->findAll();
        return $this->render("content/index.html.twig",[
            "contents" => $contents
        ]);

    }

    #[Route('/content/{id}', name: 'content_show')]
    public function show(int $id,EntityManagerInterface $manager)
    {
        $content = $manager
            ->getRepository(Content::class)
            ->findOneById($id);

        if (!isset($content) || empty($content)){
            throw $this->createNotFoundException('No content found for id ' . $id);
        } 

        $seralizedData = $this->serializer->serialize($content,"json");
        return $this->render("....");
    }
    
    #[Route('/content/create', name: 'content_create',methods:['GET','POST'])]
    public function create(EntityManagerInterface $manager,Request $request)
    {
        $content = new Content();
        $form = $this->createForm(Content::class,$content);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $content= $form->getData();
            $manager->persist($content);
            $manager->flush();
            $this->addFlash(
                'success',
                'New content has been created successfully'
            );
            return $this->redirectToRoute('content_list');
        }
        return $this->render('content/new.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    #[Route('/content/edit/{id}', name: 'content_edit',methods:['GET','POST'])]
    public function editArticle(Content $content, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(Content::class, $content);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->getData();
            $manager->persist($content);
            $manager->flush();
            $this->addFlash(
                'success',
                'Content has been modified successfully'
            );
            return $this->redirectToRoute('content_list');
        }

        return $this->render("cotent/edit_content.html.twig", [
            'form' => $form->createView()
        ]);
    }

    #[Route('/content/delete/{id}', name: 'content_delete')]
    public function deleteArticle(Content $content, EntityManagerInterface $manager)
    {
        $manager->remove($content);
        $manager->flush();
        $this->addFlash(
            'success',
            'Content has been removed successfully'
        );
        return $this->redirectToRoute('content_list');
    }
}
