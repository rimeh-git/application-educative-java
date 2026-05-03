<?php

namespace App\Tests\Service;

use App\Entity\Session;
use App\Service\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionManagerTest extends TestCase
{
    // ✅ Test 1 : Session complètement valide
    public function testValidSession(): void
    {
        $session = new Session();
        $session->setDuree(60);
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setParentEmail('parent@email.com');
        $session->setStatut('PLANIFIEE');

        $manager = new SessionManager();
        $this->assertTrue($manager->validate($session));
    }

    // ❌ Test 2 : Durée = 0 → doit échouer
    public function testSessionWithZeroDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée doit être supérieure à zéro.');

        $session = new Session();
        $session->setDuree(0);
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setStatut('PLANIFIEE');

        $manager = new SessionManager();
        $manager->validate($session);
    }

    // ❌ Test 3 : Durée négative → doit échouer
    public function testSessionWithNegativeDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée doit être supérieure à zéro.');

        $session = new Session();
        $session->setDuree(-10);
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setStatut('PLANIFIEE');

        $manager = new SessionManager();
        $manager->validate($session);
    }

    // ❌ Test 4 : Durée > 300 minutes → doit échouer
    public function testSessionWithTooLongDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée ne peut pas dépasser 300 minutes.');

        $session = new Session();
        $session->setDuree(301);
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setStatut('PLANIFIEE');

        $manager = new SessionManager();
        $manager->validate($session);
    }

    // ❌ Test 5 : Date de session null → doit échouer (CORRIGÉ avec Reflection)
    public function testSessionWithoutDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de la session est obligatoire.');

        $session = new Session();
        $session->setDuree(60);
        $session->setStatut('PLANIFIEE');

        // Forcer dateHeure à null via reflection (car setDateHeure n'accepte pas null)
        $reflection = new \ReflectionClass($session);
        $property = $reflection->getProperty('dateHeure');
        $property->setAccessible(true);
        $property->setValue($session, null);

        $manager = new SessionManager();
        $manager->validate($session);
    }

    // ❌ Test 6 : Email parent invalide → doit échouer
    public function testSessionWithInvalidParentEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'email du parent est invalide.');

        $session = new Session();
        $session->setDuree(60);
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setParentEmail('email_invalide');
        $session->setStatut('PLANIFIEE');

        $manager = new SessionManager();
        $manager->validate($session);
    }

    // ✅ Test 7 : Email parent null (optionnel) → doit passer
    public function testSessionWithNoParentEmail(): void
    {
        $session = new Session();
        $session->setDuree(60);
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setParentEmail(null);
        $session->setStatut('PLANIFIEE');

        $manager = new SessionManager();
        $this->assertTrue($manager->validate($session));
    }

    // ✅ Test 8 : Email parent vide (optionnel) → doit passer
    public function testSessionWithEmptyParentEmail(): void
    {
        $session = new Session();
        $session->setDuree(60);
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setParentEmail('');
        $session->setStatut('PLANIFIEE');

        $manager = new SessionManager();
        $this->assertTrue($manager->validate($session));
    }

    // ❌ Test 9 : Statut invalide → doit échouer
    public function testSessionWithInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut de la session est invalide.');

        $session = new Session();
        $session->setDuree(60);
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setStatut('STATUT_INVALIDE');

        $manager = new SessionManager();
        $manager->validate($session);
    }

    // ❌ Test 10 : Durée null → doit échouer
    public function testSessionWithNullDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée doit être supérieure à zéro.');

        $session = new Session();
        $session->setDateHeure(new \DateTime('2026-05-10 10:00'));
        $session->setStatut('PLANIFIEE');

        $manager = new SessionManager();
        $manager->validate($session);
    }
}