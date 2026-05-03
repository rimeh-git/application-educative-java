<?php

namespace App\Service;

class GoogleMeetService
{
    /**
     * Génère automatiquement un lien Jitsi Meet unique
     * Accès direct sans autorisation ni sécurité
     */
    public function generateMeetLink(): string
    {
        $roomId = 'session-' . bin2hex(random_bytes(8));
        return 'https://meet.jit.si/' . $roomId;
    }

    /**
     * Génère un code de sécurité pour la réunion (à partager séparément)
     */
    public function generateSecurityCode(): string
    {
        return strtoupper($this->randomString(6));
    }

    private function randomString(int $length): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }
        return $result;
    }
}
