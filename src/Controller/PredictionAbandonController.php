<?php

namespace App\Controller;

use App\Entity\Session;
use App\Service\AbandonPredictionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prediction')]
class PredictionAbandonController extends AbstractController
{
    public function __construct(
        private readonly AbandonPredictionService $predictionService,
    ) {}

    #[Route('', name: 'app_prediction_dashboard')]
    public function dashboard(): Response
    {
        $analyse = $this->predictionService->analyserTout();

        return $this->render('prediction/dashboard.html.twig', [
            'resultats'  => $analyse['resultats'],
            'total'      => $analyse['total'],
            'stables'    => $analyse['stables'],
            'risques'    => $analyse['risques'],
            'tauxRisque' => $analyse['tauxRisque'],
        ]);
    }

    #[Route('/session/{id}', name: 'app_prediction_session', methods: ['GET'])]
    public function predireSession(Session $session): JsonResponse
    {
        $result = $this->predictionService->predire($session);

        return new JsonResponse([
            'session_id'  => $session->getId(),
            'prediction'  => $result['prediction'],
            'statut'      => $result['statut'],
            'label'       => $result['label'],
            'probabilite' => $result['probabilite'],
            'facteurs'    => $result['facteurs'],
        ]);
    }
}
