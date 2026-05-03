<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\Session;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rating')]
final class RatingController extends AbstractController
{
    private function getUserIdentifier(Request $request): string
    {
        return hash('sha256', $request->getClientIp() . $request->headers->get('User-Agent'));
    }

    /** @return array<string, mixed> */
    private function getStats(Session $session, RatingRepository $ratingRepo): array
    {
        $likes    = $ratingRepo->count(['session' => $session, 'rating' => 1]);
        $dislikes = $ratingRepo->count(['session' => $session, 'rating' => -1]);
        $total    = $likes + $dislikes;
        return [
            'likes'          => $likes,
            'dislikes'       => $dislikes,
            'total'          => $total,
            'satisfaction'   => $total > 0 ? round(($likes / $total) * 100) : null,
        ];
    }

    #[Route('/session/{id}/rate', name: 'app_rating_rate', methods: ['POST'])]
    public function rate(Session $session, Request $request, EntityManagerInterface $em, RatingRepository $ratingRepo): JsonResponse
    {
        $ratingValue = (int) $request->request->get('rating');
        if (!in_array($ratingValue, [1, -1])) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid rating'], 400);
        }

        $userIdentifier = $this->getUserIdentifier($request);
        $existing = $ratingRepo->findOneBy(['session' => $session, 'userIdentifier' => $userIdentifier]);

        if ($existing) {
            $existing->setRating($ratingValue);
        } else {
            $rating = (new Rating())->setSession($session)->setUserIdentifier($userIdentifier)->setRating($ratingValue);
            $em->persist($rating);
        }
        $em->flush();

        return new JsonResponse(['success' => true, 'userVote' => $ratingValue] + $this->getStats($session, $ratingRepo));
    }

    #[Route('/session/{id}/stats', name: 'app_rating_stats', methods: ['GET'])]
    public function stats(Session $session, Request $request, RatingRepository $ratingRepo): JsonResponse
    {
        $userIdentifier = $this->getUserIdentifier($request);
        $existing = $ratingRepo->findOneBy(['session' => $session, 'userIdentifier' => $userIdentifier]);

        return new JsonResponse(['userVote' => $existing?->getRating()] + $this->getStats($session, $ratingRepo));
    }
}
