<?php

namespace App\Service;

use App\Entity\SuiviProgression;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class ProgressReportEmailService
{
    public function __construct(
        private MailerInterface       $mailer,
        private LoggerInterface       $logger,
        private UrlGeneratorInterface $urlGenerator,
        private string                $adminEmail,
        private string                $adminName,
    ) {}

    /** @return array<string, mixed> */
    public function sendProgressReport(SuiviProgression $suivi, string $parentEmail): array
    {
        try {
            // Correction PHPStan : getSession() peut retourner null
            $session = $suivi->getSession();
            if ($session === null) {
                return ['success' => false, 'error' => 'Session introuvable'];
            }

            $dateHeure = $session->getDateHeure();
            // Correction PHPStan : format() sur DateTimeInterface|null
            $dateStr = $dateHeure !== null ? $dateHeure->format('d/m/Y') : 'date inconnue';

            $pdfUrl = $this->urlGenerator->generate(
                'app_suivi_progression_print',
                ['id' => $suivi->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from(new Address($this->adminEmail, $this->adminName))
                ->to($parentEmail)
                ->subject('Rapport de progression de votre enfant - Session du ' . $dateStr)
                ->html($this->buildHtml($suivi, $pdfUrl));

            $this->mailer->send($email);

            $this->logger->info('Rapport envoye', [
                'to'       => $parentEmail,
                'suivi_id' => $suivi->getId(),
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi rapport', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function buildHtml(SuiviProgression $suivi, string $pdfUrl): string
    {
        // Correction PHPStan : getSession() peut retourner null
        $session = $suivi->getSession();
        if ($session === null) {
            return '<p>Session introuvable</p>';
        }

        $dateHeure = $session->getDateHeure();
        $dateStr   = $dateHeure !== null ? $dateHeure->format('d/m/Y \a\t H:i') : 'date inconnue';

        // Correction PHPStan : getType() peut retourner null
        $type = $session->getType();

        $prog      = $suivi->getProgression() ?? 0;
        $progColor = $prog > 0 ? '#10b981' : ($prog < 0 ? '#ef4444' : '#6b7280');
        $progSign  = $prog > 0 ? '+' : '';
        $progLabel = $prog > 0 ? 'Progression positive' : ($prog < 0 ? 'Regression' : 'Stable');

        $comportementsText = $suivi->getComportementsObserves();
        $comportements = $comportementsText
            ? '<div style="background:#fff;border-radius:8px;padding:16px;margin-top:16px;border-left:4px solid #6366f1">
                <p style="font-weight:700;color:#4f46e5;margin:0 0 8px">Comportements observes</p>
                <p style="margin:0;color:#374151">' . nl2br(htmlspecialchars($comportementsText)) . '</p>
               </div>'
            : '';

        $declencheursText = $suivi->getDeclencheursIdentifies();
        $declencheurs = $declencheursText
            ? '<div style="background:#fff;border-radius:8px;padding:16px;margin-top:16px;border-left:4px solid #f59e0b">
                <p style="font-weight:700;color:#d97706;margin:0 0 8px">Declencheurs identifies</p>
                <p style="margin:0;color:#374151">' . nl2br(htmlspecialchars($declencheursText)) . '</p>
               </div>'
            : '';

        $recommandationsText = $suivi->getRecommandationsParent();
        $recommandations = $recommandationsText
            ? '<div style="background:#fff;border-radius:8px;padding:16px;margin-top:16px;border-left:4px solid #10b981">
                <p style="font-weight:700;color:#059669;margin:0 0 8px">Recommandations pour les parents</p>
                <p style="margin:0;color:#374151">' . nl2br(htmlspecialchars($recommandationsText)) . '</p>
               </div>'
            : '';

        return '<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif">
<div style="max-width:600px;margin:32px auto;background:#fff;border-radius:16px;overflow:hidden">
    <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:36px 32px;text-align:center">
        <h1 style="color:#fff;margin:0;font-size:22px;font-weight:800">Rapport de Progression</h1>
        <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px">Evolution de votre enfant</p>
    </div>
    <div style="padding:32px">
        <div style="background:#f8fafc;border-radius:12px;padding:20px;margin-bottom:24px;border:1px solid #e2e8f0">
            <p style="margin:0 0 6px;font-size:16px;font-weight:800;color:#1e293b">' . $dateStr . '</p>
            <p style="margin:0;font-size:14px;color:#64748b">Type : <strong>' . htmlspecialchars($type ?? 'Non specifie') . '</strong> &bull; Domaine : <strong>' . htmlspecialchars((string)$suivi->getDomaine()) . '</strong></p>
        </div>
        <div style="display:flex;gap:12px;margin-bottom:24px">
            <div style="flex:1;background:#fef2f2;border-radius:12px;padding:20px;text-align:center;border:2px solid #fecaca">
                <p style="margin:0 0 4px;font-size:12px;font-weight:700;color:#ef4444">Score Avant</p>
                <p style="margin:0;font-size:36px;font-weight:900;color:#ef4444">' . $suivi->getScoreAvant() . '/10</p>
            </div>
            <div style="display:flex;align-items:center;font-size:24px;color:#94a3b8">&#8594;</div>
            <div style="flex:1;background:#f0fdf4;border-radius:12px;padding:20px;text-align:center;border:2px solid #bbf7d0">
                <p style="margin:0 0 4px;font-size:12px;font-weight:700;color:#10b981">Score Apres</p>
                <p style="margin:0;font-size:36px;font-weight:900;color:#10b981">' . $suivi->getScoreApres() . '/10</p>
            </div>
        </div>
        <div style="background:' . $progColor . ';border-radius:12px;padding:16px 24px;text-align:center;margin-bottom:24px">
            <p style="margin:0;color:#fff;font-size:20px;font-weight:900">Progression : ' . $progSign . $prog . ' points</p>
            <p style="margin:4px 0 0;color:rgba(255,255,255,0.85);font-size:13px">' . $progLabel . '</p>
        </div>
        ' . $comportements . $declencheurs . $recommandations . '
        <div style="text-align:center;margin-top:32px;padding:24px;background:#f8fafc;border-radius:12px;border:2px dashed #e2e8f0">
            <p style="margin:0 0 20px;font-size:14px;font-weight:700;color:#374151">Rapport complet disponible en PDF</p>
            <a href="' . $pdfUrl . '" style="display:inline-block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;text-decoration:none;padding:14px 32px;border-radius:10px;font-size:15px;font-weight:800">
                Voir le rapport PDF
            </a>
        </div>
    </div>
    <div style="background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e2e8f0">
        <p style="margin:0;font-size:13px;font-weight:700;color:#4f46e5">' . htmlspecialchars($this->adminName) . '</p>
    </div>
</div>
</body></html>';
    }
}
