<?php

namespace App\Controller;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class MediaController extends AbstractController
{
    #[Route('/media', name: 'media')]
    public function index(): Response
    {
        return $this->render('media/index.html.twig', [
            'controller_name' => 'MediaController',
        ]);
    }

    #[Route('/media/list', name: 'media_list')]
    public function list(EntityManagerInterface $manager)
    {
        $medias = $manager->getRepository(Media::class)->findAll();

        if (!isset($medias) || empty($medias)) {
            throw $this->createNotFoundException('No media found');
        }
        return $this->render("media/list.html.twig", [
            "medias" => $medias
        ]);
    }

    #[Route('/media/create', name: 'media_create', methods: ['GET', 'POST'])]
    public function create(EntityManagerInterface $manager, Request $request)
    {
        $media = new Media();
        $form = $this->createForm(Media::class, $media);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $media = $form->getData();
            $manager->persist($media);
            $manager->flush();
            $this->addFlash(
                'success',
                'New media has been created successfully'
            );
            return $this->redirectToRoute('media_list');
        }
        return $this->render('media/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/media/edit/{id}', name: 'media_edit')]
    public function editArticle(Media $media, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(Media::class, $media);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $media = $form->getData();
            $manager->persist($media);
            $manager->flush();
            $this->addFlash(
                'success',
                'Media has been modified successfully'
            );
            return $this->redirectToRoute('media_list');
        }

        return $this->render("media/edit_media.html.twig", [
            'form' => $form->createView()
        ]);
    }

    #[Route('/media/delete/{id}', name: 'media_delete')]
    public function deleteArticle(Media $media, EntityManagerInterface $manager)
    {
        $manager->remove($media);
        $manager->flush();
        $this->addFlash(
            'success',
            'Media has been removed successfully'
        );
        return $this->redirectToRoute('media_list');
    }
}
