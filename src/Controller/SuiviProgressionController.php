<?php

namespace App\Controller;

use App\Entity\SuiviProgression;
use App\Form\SuiviProgressionType;
use App\Repository\SuiviProgressionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/suivi/progression')]
final class SuiviProgressionController extends AbstractController
{
    #[Route(name: 'app_suivi_progression_index', methods: ['GET'])]
    public function index(SuiviProgressionRepository $suiviProgressionRepository): Response
    {
        return $this->render('suivi_progression/index.html.twig', [
            'suivis' => $suiviProgressionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_suivi_progression_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $suiviProgression = new SuiviProgression();
        $form = $this->createForm(SuiviProgressionType::class, $suiviProgression);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($suiviProgression);
            $entityManager->flush();

            $this->addFlash('success', '✅ Suivi créé avec succès !');
            return $this->redirectToRoute('app_suivi_progression_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suivi_progression/new.html.twig', [
            'suivi' => $suiviProgression,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_suivi_progression_show', methods: ['GET'])]
    public function show(SuiviProgression $suiviProgression): Response
    {
        return $this->render('suivi_progression/show.html.twig', [
            'suivi' => $suiviProgression,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_suivi_progression_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SuiviProgression $suiviProgression, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SuiviProgressionType::class, $suiviProgression);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', '✅ Suivi mis à jour avec succès !');
            return $this->redirectToRoute('app_suivi_progression_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suivi_progression/edit.html.twig', [
            'suivi' => $suiviProgression,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_suivi_progression_delete', methods: ['POST'])]
    public function delete(Request $request, SuiviProgression $suiviProgression, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$suiviProgression->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($suiviProgression);
            $entityManager->flush();

            $this->addFlash('success', '🗑️ Suivi supprimé.');
        }

        return $this->redirectToRoute('app_suivi_progression_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/print', name: 'app_suivi_progression_print', methods: ['GET'])]
public function print(SuiviProgression $suiviProgression): Response
{
    return $this->render('suivi_progression/print.html.twig', [
        'suivi' => $suiviProgression,
    ]);
}
}