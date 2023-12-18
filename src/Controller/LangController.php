<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Lang;
use App\Form\LangType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class LangController extends AbstractController
{

    #[Route('/lang/list', name: 'lang_list')]
    public function list(EntityManagerInterface $manager)
    {
        $langs = $manager->getRepository(Lang::class)->findAll();

        if (!isset($langs) || empty($langs)) {
            throw $this->createNotFoundException('No lang found');
        }
        return $this->render("lang/list.html.twig", [
            "langs" => $langs
        ]);
    }

    #[Route('/lang/create', name: 'lang_create', methods: ['GET', 'POST'])]
    public function createLang(EntityManagerInterface $manager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $lang = new Lang();
            $code = $request->request->get('code');
            $name = $request->request->get('lang');
            if ($manager->getRepository(Lang::class)->checkExisted($code, $name)) {
                $lang->setCode($code);
                $lang->setLang($name);
                $manager->persist($lang);
                $manager->flush();
                $this->addFlash(
                    'success',
                    'Le nouveau langage a été ajouté'
                );
                return $this->redirectToRoute('lang_list');
            } else {
                $this->addFlash(
                    'success',
                    'Le code ou lang a existé'
                );
                return $this->redirectToRoute('lang_list');
            }
        }
    }

    #[Route('/lang/edit/{id}', name: 'lang_edit', methods: ['GET', 'POST'])]
    public function editArticle(Lang $lang, Request $request, EntityManagerInterface $manager): Response
    {
        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');
            $name = $request->request->get('lang');
            $lang->setCode($code);
            $lang->setLang($name);
            $manager->persist($lang);
            $manager->flush();
            $this->addFlash(
                'success',
                'La langue a été modifiée '
            );
            return $this->redirectToRoute('lang_list');
        }
    }

    #[Route('/lang/delete/{id}', name: 'lang_delete')]
    public function deleteArticle(Lang $lang, EntityManagerInterface $manager)
    {
        $manager->remove($lang);
        $manager->flush();
        $this->addFlash(
            'success',
            'La langue a été supprimée'
        );
        return $this->redirectToRoute('lang_list');
    }
}
