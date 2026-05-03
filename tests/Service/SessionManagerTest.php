<?php

namespace App\Tests\Service;

use App\Entity\Session;
use App\Service\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionManagerTest extends TestCase
{
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

    public function testSessionWithoutDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de la session est obligatoire.');

        $session = new Session();
        $session->setDuree(60);
        $session->setStatut('PLANIFIEE');

        $reflection = new \ReflectionClass($session);
        $property = $reflection->getProperty('dateHeure');
        $property->setAccessible(true);
        $property->setValue($session, null);

        $manager = new SessionManager();
        $manager->validate($session);
    }

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
