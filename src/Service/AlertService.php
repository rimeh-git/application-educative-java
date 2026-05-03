<?php

namespace App\Service;

use App\Entity\Patient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\ChatterInterface;

class AlertService
{
    public function __construct(
        private LoggerInterface  $logger,
        private MailerInterface  $mailer,
        private ?ChatterInterface $chatter = null
    ) {}

    /** @param array<string, mixed> $prediction */
    public function declencherAlerte(Patient $patient, array $prediction): void
    {
        $this->logger->warning(sprintf(
            'ALERTE RISQUE ABANDON - Patient: %s %s (ID: %d) - Score: %.2f%%',
            $patient->getPrenom(),
            $patient->getNom(),
            (int) $patient->getId(),
            (float) ($prediction['score'] ?? 0) * 100
        ));
    }

    /** @return array<int, mixed> */
    public function getAlertesActives(): array
    {
        return [];
    }
}
