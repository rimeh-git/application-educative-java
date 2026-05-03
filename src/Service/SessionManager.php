<?php

namespace App\Service;

use App\Entity\Session;

class SessionManager
{
    public function validate(Session $session): bool
    {
        if ($session->getDuree() === null || $session->getDuree() <= 0) {
            throw new \InvalidArgumentException('La durée doit être supérieure à zéro.');
        }

        if ($session->getDuree() > 300) {
            throw new \InvalidArgumentException('La durée ne peut pas dépasser 300 minutes.');
        }

        if ($session->getDateHeure() === null) {
            throw new \InvalidArgumentException('La date de la session est obligatoire.');
        }

        $email = $session->getParentEmail();
        if ($email !== null && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('L\'email du parent est invalide.');
        }

        if (!in_array($session->getStatut(), Session::STATUTS, true)) {
            throw new \InvalidArgumentException('Le statut de la session est invalide.');
        }

        return true;
    }
}