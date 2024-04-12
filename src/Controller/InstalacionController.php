<?php

namespace App\Controller;

use App\Entity\Instalacion;
use App\Form\InstalacionType;
use App\Repository\InstalacionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/instalacion')]
class InstalacionController extends AbstractController
{
    #[Route('/', name: 'app_instalacion_index', methods: ['GET'])]
    public function index(InstalacionRepository $instalacionRepository): Response
    {
        return $this->render('instalacion/index.html.twig', [
            'instalacions' => $instalacionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_instalacion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $instalacion = new Instalacion();
        $form = $this->createForm(InstalacionType::class, $instalacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($instalacion);
            $entityManager->flush();

            return $this->redirectToRoute('app_instalacion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('instalacion/new.html.twig', [
            'instalacion' => $instalacion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_instalacion_show', methods: ['GET'])]
    public function show(Instalacion $instalacion): Response
    {
        return $this->render('instalacion/show.html.twig', [
            'instalacion' => $instalacion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_instalacion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Instalacion $instalacion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InstalacionType::class, $instalacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_instalacion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('instalacion/edit.html.twig', [
            'instalacion' => $instalacion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_instalacion_delete', methods: ['POST'])]
    public function delete(Request $request, Instalacion $instalacion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$instalacion->getId(), $request->request->get('_token'))) {
            $entityManager->remove($instalacion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_instalacion_index', [], Response::HTTP_SEE_OTHER);
    }
}
