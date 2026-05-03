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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\ProgressReportEmailService;

#[Route('/suivi/progression')]
final class SuiviProgressionController extends AbstractController
{
    // ============================================
    // LISTE - Avec Pagination + Recherche + Tri
    // ============================================
    #[Route(name: 'app_suivi_progression_index', methods: ['GET'])]
    public function index(Request $request, SuiviProgressionRepository $suiviProgressionRepository): Response
    {
        // ---------- 1. RÉCUPÉRATION PARAMÈTRES ----------
        
        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = max(1, min(50, $request->query->getInt('limit', 10)));
        
        // Tri
        $sortBy = $request->query->get('sortBy', 'dateEvaluation');
        $sortOrder = $request->query->get('sortOrder', 'DESC');

        // Filtres
        $filters = [
            'search' => $request->query->get('search'),
            'domaine' => $request->query->get('domaine'),
        ];
        // Nettoyer les filtres vides
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        // ---------- 2. RÉCUPÉRATION DONNÉES ----------
        
        // Les suivis pour la page actuelle (avec pagination)
        $suiviProgressions = $suiviProgressionRepository->findByFilters(
            $filters,
            $sortBy,
            $sortOrder,
            $page,
            $limit
        );
        
        // Nombre total de suivis (pour calculer les pages)
        $totalItems = $suiviProgressionRepository->countByFilters($filters);
        
        // Calcul nombre total de pages
        $totalPages = (int) ceil($totalItems / $limit);

        // ---------- 3. RÉPONSE TWIG ----------
        
        return $this->render('suivi_progression/index.html.twig', [
            'suiviProgressions' => $suiviProgressions,  // ← CORRIGÉ
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'page' => $page,
            'limit' => $limit,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'filters' => $filters,
        ]);
    }

    // ============================================
    // CRÉER - Nouveau suivi
    // ============================================
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

    // ============================================
    // AFFICHER - Détails d'un suivi
    // ============================================
    #[Route('/{id}', name: 'app_suivi_progression_show', methods: ['GET'])]
    public function show(SuiviProgression $suiviProgression): Response
    {
        return $this->render('suivi_progression/show.html.twig', [
            'suivi' => $suiviProgression,
        ]);
    }

    // ============================================
    // MODIFIER - Éditer un suivi
    // ============================================
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

    // ============================================
    // SUPPRIMER - Delete un suivi
    // ============================================
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

    // ============================================
    // EMAIL RAPPORT - Envoyer rapport par email
    // ============================================
    #[Route('/{id}/email-report', name: 'app_suivi_progression_email_report', methods: ['POST'])]
    public function emailReport(SuiviProgression $suiviProgression, ProgressReportEmailService $progressReportEmailService): Response
    {
        try {
            // Acces securise a la session (peut etre null)
            $session = $suiviProgression->getSession();
            if ($session === null) {
                $this->addFlash('warning', 'Aucune session associee a ce suivi');
                return $this->redirectToRoute('app_suivi_progression_index');
            }

            $parentEmail = $session->getParentEmail();
            if (!$parentEmail || $parentEmail === 'parent@example.com') {
                $this->addFlash('warning', 'Aucun email parent defini pour cette session');
                return $this->redirectToRoute('app_suivi_progression_index');
            }

            $result = $progressReportEmailService->sendProgressReport($suiviProgression, $parentEmail);

            $this->addFlash($result['success'] ? 'success' : 'error',
                $result['success'] ? '📧 Rapport de progression envoyé avec succès' : '❌ Échec de l\'envoi du rapport');
        } catch (\Exception $e) {
            $this->addFlash('error', '❌ Erreur lors de l\'envoi du rapport: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_suivi_progression_index');
    }

    // ============================================
    // IMPRIMER - Version imprimable
    // ============================================
    #[Route('/{id}/print', name: 'app_suivi_progression_print', methods: ['GET'])]
    public function print(SuiviProgression $suiviProgression): Response
    {
        return $this->render('suivi_progression/print.html.twig', [
            'suivi' => $suiviProgression,
        ]);
    }

   
}