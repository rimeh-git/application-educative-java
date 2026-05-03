<?php

namespace App\Service;

use App\Entity\Session;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class EmailRappelService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $adminEmail,
        private string $adminName,
        private string $projectDir,
    ) {}

    /** @return array<string, mixed> */
    public function notifyParentAboutSession(Session $session, string $parentEmail): array
    {
        try {
            $dateHeure = $session->getDateHeure();
            $subject   = $dateHeure !== null
                ? 'Votre session therapeutique du ' . $dateHeure->format('d/m/Y') . ' - Details et lien Meet'
                : 'Votre session therapeutique - Details et lien Meet';

            $email = (new Email())
                ->from(new Address($this->adminEmail, $this->adminName))
                ->to($parentEmail)
                ->subject($subject)
                ->html($this->buildTemplate($session));

            $this->mailer->send($email);

            $this->logger->info('Email session envoye', [
                'to'      => $parentEmail,
                'session' => $session->getId(),
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            $this->logger->error('Erreur email session', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function buildTemplate(Session $session): string
    {
        $dateHeure = $session->getDateHeure();
        $dateFr    = $dateHeure ? $dateHeure->format('d/m/Y') : '-';
        $heureFr   = $dateHeure ? $dateHeure->format('H:i') : '-';
        $duree     = $session->getDuree() ?? 0;
        $type      = htmlspecialchars($session->getType() ?? 'Non specifie');
        $meetLink  = $session->getMeetLink();

        $heureFin = '-';
        if ($dateHeure instanceof \DateTime && $duree > 0) {
            $fin = clone $dateHeure;
            $fin->modify("+{$duree} minutes");
            $heureFin = $fin->format('H:i');
        }

        // Bloc Google Meet
        $meetBlock = '';
        if ($meetLink) {
            $meetBlock = '
            <div style="background:linear-gradient(135deg,#1a73e8,#0d47a1);border-radius:14px;padding:28px;text-align:center;margin:28px 0">
                <div style="font-size:36px;margin-bottom:10px">&#x1F4F9;</div>
                <p style="color:#fff;font-size:17px;font-weight:800;margin:0 0 6px">Rejoindre la Visioconférence</p>
                <p style="color:rgba(255,255,255,0.75);font-size:13px;margin:0 0 18px">
                    Cliquez sur le bouton pour rejoindre Google Meet à l\'heure de la session
                </p>
                <a href="' . htmlspecialchars($meetLink) . '"
                   style="display:inline-block;background:#ffffff;color:#1a73e8;text-decoration:none;padding:13px 30px;border-radius:8px;font-size:15px;font-weight:800;letter-spacing:.02em">
                    &#x1F4F9; Rejoindre Google Meet
                </a>
                <p style="color:rgba(255,255,255,0.5);font-size:11px;margin:14px 0 0">
                    ' . htmlspecialchars($meetLink) . '
                </p>
            </div>';
        }

        return '<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif">
<div style="max-width:600px;margin:32px auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08)">

    <!-- HEADER -->
    <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:36px 32px;text-align:center">
        <div style="font-size:40px;margin-bottom:12px">&#x1F4C5;</div>
        <h1 style="color:#fff;margin:0;font-size:22px;font-weight:800">Rappel de Session Thérapeutique</h1>
        <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px">' . $dateFr . ' à ' . $heureFr . '</p>
    </div>

    <!-- BODY -->
    <div style="padding:32px">

        <p style="font-size:16px;color:#374151;margin:0 0 24px;line-height:1.6">
            Bonjour,<br><br>
            Vous avez une <strong>session thérapeutique</strong> prévue le
            <strong style="color:#4f46e5">' . $dateFr . '</strong> à
            <strong style="color:#4f46e5">' . $heureFr . '</strong>.
        </p>

        <!-- Details session -->
        <div style="background:#f8fafc;border-radius:12px;padding:20px;border:1px solid #e2e8f0;margin-bottom:24px">
            <p style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin:0 0 14px">
                Détails de la session
            </p>
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="padding:9px 0;color:#64748b;font-size:14px;width:40%">Type de thérapie</td>
                    <td style="padding:9px 0;font-weight:700;color:#1e293b;font-size:14px">' . $type . '</td>
                </tr>
                <tr style="border-top:1px solid #e2e8f0">
                    <td style="padding:9px 0;color:#64748b;font-size:14px">Date</td>
                    <td style="padding:9px 0;font-weight:700;color:#1e293b;font-size:14px">' . $dateFr . '</td>
                </tr>
                <tr style="border-top:1px solid #e2e8f0">
                    <td style="padding:9px 0;color:#64748b;font-size:14px">Heure de début</td>
                    <td style="padding:9px 0;font-weight:700;color:#1e293b;font-size:14px">' . $heureFr . '</td>
                </tr>
                <tr style="border-top:1px solid #e2e8f0">
                    <td style="padding:9px 0;color:#64748b;font-size:14px">Heure de fin</td>
                    <td style="padding:9px 0;font-weight:700;color:#1e293b;font-size:14px">' . $heureFin . '</td>
                </tr>
                <tr style="border-top:1px solid #e2e8f0">
                    <td style="padding:9px 0;color:#64748b;font-size:14px">Durée</td>
                    <td style="padding:9px 0;font-weight:700;color:#1e293b;font-size:14px">' . $duree . ' minutes</td>
                </tr>
            </table>
        </div>

        ' . $meetBlock . '

        <p style="text-align:center;color:#94a3b8;font-size:13px;margin-top:24px">
            Cet email a été envoyé automatiquement par TSA Thérapie.<br>
            Pour toute question, contactez votre thérapeute.
        </p>
    </div>

    <!-- FOOTER -->
    <div style="background:#f8fafc;padding:18px 32px;text-align:center;border-top:1px solid #e2e8f0">
        <p style="margin:0;font-size:13px;font-weight:700;color:#4f46e5">' . htmlspecialchars($this->adminName) . '</p>
        <p style="margin:4px 0 0;font-size:12px;color:#94a3b8">Centre TSA Thérapie</p>
    </div>

</div>
</body>
</html>';
    }
}
