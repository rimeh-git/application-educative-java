<?php

namespace App\EventListener;

use App\Entity\SuiviProgression;
use App\Repository\SessionRepository;
use App\Service\RisqueAbandonService;

class SuiviProgressionListener
{
    public function __construct(
        private RisqueAbandonService $riskService,
        private SessionRepository    $sessionRepo,
    ) {}

    public function postPersist(SuiviProgression $entity): void
    {
        if (!$entity->getSession()) return;

        $sessions = $this->sessionRepo->findAll();
        $analyse  = $this->riskService->analyser($sessions);

        // Log si risque critique
        if ($analyse['score'] >= 75) {
            error_log(sprintf(
                '[RisqueAbandon] Score critique: %d%% (%s) après suivi #%d',
                $analyse['score'],
                $analyse['niveau'],
                $entity->getId()
            ));
        }
    }
}
