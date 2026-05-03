<?php

namespace App\Service;

use App\Entity\Session;
use Twilio\Rest\Client;
use DateTime;

class TwilioService
{
    private ?Client $client = null;

    public function __construct(
        private readonly string $accountSid,
        private readonly string $authToken,
        private readonly string $fromNumber
    ) {}

    /** @return array<string, mixed> */
    public function notifyParSession(string $phoneNumber, Session $session, string $statut): array
    {
        return match ($statut) {
            'PLANIFIEE' => $this->smsPlanifiee($phoneNumber, $session),
            'EN_COURS'  => $this->smsEnCours($phoneNumber, $session),
            'TERMINEE'  => $this->smsTerminee($phoneNumber, $session),
            'ANNULEE'   => $this->smsAnnulee($phoneNumber, $session),
            default     => ['success' => false, 'error' => 'Statut inconnu : ' . $statut, 'type' => 'INCONNU'],
        };
    }

    /** @return array<string, mixed> */
    private function smsPlanifiee(string $phoneNumber, Session $session): array
    {
        $date  = $this->formatDate($session->getDateHeure());
        $type  = $session->getType() ?? 'therapie';
        $duree = $session->getDuree() ?? 0;

        $message  = "Votre seance de " . $type . "\n";
        $message .= "prevue le " . $date . "\n";
        $message .= "Duree: " . $duree . " min\n";
        $message .= "\n-- Equipe TSA Therapie";

        return $this->envoyer($phoneNumber, $message, 'RAPPEL_PLANIFIEE');
    }

    /** @return array<string, mixed> */
    private function smsEnCours(string $phoneNumber, Session $session): array
    {
        $type  = $session->getType() ?? 'therapie';
        $duree = $session->getDuree() ?? 0;

        $message  = "Votre seance de " . $type . "\n";
        $message .= "a commence\n";
        $message .= "Duree: " . $duree . " min\n";
        $message .= "\n-- Equipe TSA Therapie";

        return $this->envoyer($phoneNumber, $message, 'INFO_EN_COURS');
    }

    /** @return array<string, mixed> */
    private function smsTerminee(string $phoneNumber, Session $session): array
    {
        $type  = $session->getType() ?? 'therapie';
        $duree = $session->getDuree() ?? 0;

        $message  = "Votre seance de " . $type . "\n";
        $message .= "est terminee\n";
        $message .= "Duree: " . $duree . " min\n";
        $message .= "\nMerci pour votre participation\n";
        $message .= "\n-- Equipe TSA Therapie";

        return $this->envoyer($phoneNumber, $message, 'COMPTE_RENDU_TERMINEE');
    }

    /** @return array<string, mixed> */
    private function smsAnnulee(string $phoneNumber, Session $session): array
    {
        $date = $this->formatDate($session->getDateHeure());
        $type = $session->getType() ?? 'therapie';

        $message  = "Votre seance de " . $type . "\n";
        $message .= "prevue le " . $date . "\n";
        $message .= "a ete ANNULEE\n";
        $message .= "\nNous vous recontacterons\n";
        $message .= "\n-- Equipe TSA Therapie";

        return $this->envoyer($phoneNumber, $message, 'AVIS_ANNULATION');
    }

    /** @return array<string, mixed> */
    public function sendSms(string $phoneNumber, string $title, \DateTimeInterface|string $dateHeure, ?int $duree): array
    {
        $dateStr = $dateHeure instanceof \DateTimeInterface ? $dateHeure->format('d/m H:i') : (string) $dateHeure;
        $message = "RAPPEL SESSION\n" . $title . "\n" . $dateStr . "\n" . ($duree ?? 0) . " min";
        return $this->envoyer($phoneNumber, $message, 'RAPPEL_GENERIQUE');
    }

    /** @param array<string, mixed> $sessionData @return array<string, mixed> */
    public function notifierSession(string $telephone, array $sessionData): array
    {
        return $this->sendSms(
            $telephone,
            (string) ($sessionData['title'] ?? 'Session'),
            $sessionData['startDate'] instanceof \DateTimeInterface ? $sessionData['startDate'] : new DateTime(),
            isset($sessionData['duration']) ? (int) $sessionData['duration'] : 0
        );
    }

    /** @return array<string, mixed> */
    public function sendTestSms(string $phoneNumber): array
    {
        return $this->sendSms($phoneNumber, 'TEST TWILIO', new DateTime(), 30);
    }

    public function isConfigured(): bool
    {
        return !empty($this->accountSid) && !empty($this->authToken) && !empty($this->fromNumber);
    }

    /** @return array<string, mixed> */
    private function envoyer(string $phoneNumber, string $message, string $type): array
    {
        // Vérifier si Twilio est configuré
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Twilio non configuré - Vérifiez vos identifiants', 'type' => $type];
        }
        
        // Vérifier le format du numéro FROM
        if (!str_starts_with($this->fromNumber, '+')) {
            return ['success' => false, 'error' => 'TWILIO_FROM_NUMBER doit être au format international (+216...)', 'type' => $type];
        }
        
        try {
            $phone = $this->formatPhone($phoneNumber);
            $sid   = $this->getClient()->messages->create($phone, [
                'from' => $this->fromNumber,
                'body' => $message,
            ]);
            return ['success' => true, 'sid' => $sid->sid, 'type' => $type];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'type' => $type];
        }
    }

    // Correction PHPStan : accepte null (DateTimeInterface|null)
    private function formatDate(\DateTimeInterface|string|null $dateHeure): string
    {
        if ($dateHeure instanceof \DateTimeInterface) {
            return $dateHeure->format('d/m/Y a H:i');
        }
        return (string) $dateHeure;
    }

    private function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client($this->accountSid, $this->authToken);
        }
        return $this->client;
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if (strlen($phone) === 8 && in_array($phone[0] ?? '', ['2', '5', '7', '9'])) {
            return '+216' . $phone;
        }
        if (str_starts_with($phone, '216') && strlen($phone) === 11) {
            return '+' . $phone;
        }
        return $phone;
    }
}
