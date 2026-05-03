<?php
// src/Entity/Session.php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'session_therapie')]
#[ORM\HasLifecycleCallbacks]
class Session
{
    public const TYPES = ['ABA', 'PECS', 'Orthophonie', 'Psychomotricité', 'Ergothérapie'];
    public const STATUTS = ['PLANIFIEE', 'EN_COURS', 'TERMINEE', 'ANNULEE'];
    public const NIVEAUX_AGITATION = ['CALME', 'AGITE', 'CRISE'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int|null $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $dateHeure = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(300)]
    private ?int $duree = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: Session::TYPES)]  // ✅ CORRIGÉ
    private ?string $type = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: Session::STATUTS)]  // ✅ CORRIGÉ
    private string $statut = 'PLANIFIEE';

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: Session::NIVEAUX_AGITATION)]  // ✅ CORRIGÉ
    private ?string $niveauAgitation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $techniqueUtilisee = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectifSeance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: SuiviProgression::class, orphanRemoval: true)]
    private Collection $suiviProgressions;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephoneResponsable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomResponsable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $parentEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $calendarEventId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $meetLink = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $meetSecurityCode = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $favori = false;

    #[ORM\Column(options: ['default' => 0])]
    private int $pointsPresence = 0;

    #[ORM\Column(length: 64, nullable: true, unique: true)]
    private ?string $qrToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $presenceConfirmeeAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $feedbackTherapeute = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $feedbackAt = null;

    public function __construct()
    {
        $this->suiviProgressions = new ArrayCollection();
        $this->dateHeure = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function __toString(): string
    {
        return sprintf(
            'Session #%d - %s (%s)',
            $this->id ?? 0,
            $this->dateHeure?->format('d/m/Y H:i') ?? 'N/A',
            $this->type ?? 'N/A'
        );
    }

    public function getDureeFormatee(): string
    {
        return ($this->duree ?? 0) . ' min';
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getDateHeure(): ?\DateTimeInterface
    {
        return $this->dateHeure;
    }

    public function setDateHeure(\DateTimeInterface $dateHeure): static
    {
        $this->dateHeure = $dateHeure;
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNiveauAgitation(): ?string
    {
        return $this->niveauAgitation;
    }

    public function setNiveauAgitation(string $niveauAgitation): static
    {
        $this->niveauAgitation = $niveauAgitation;
        return $this;
    }

    public function getTechniqueUtilisee(): ?string
    {
        return $this->techniqueUtilisee;
    }

    public function setTechniqueUtilisee(?string $techniqueUtilisee): static
    {
        $this->techniqueUtilisee = $techniqueUtilisee;
        return $this;
    }

    public function getObjectifSeance(): ?string
    {
        return $this->objectifSeance;
    }

    public function setObjectifSeance(?string $objectifSeance): static
    {
        $this->objectifSeance = $objectifSeance;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, SuiviProgression>
     */
    public function getSuiviProgressions(): Collection
    {
        return $this->suiviProgressions;
    }

    public function addSuiviProgression(SuiviProgression $suiviProgression): static
    {
        if (!$this->suiviProgressions->contains($suiviProgression)) {
            $this->suiviProgressions->add($suiviProgression);
            $suiviProgression->setSession($this);
        }
        return $this;
    }

    public function removeSuiviProgression(SuiviProgression $suiviProgression): static
    {
        if ($this->suiviProgressions->removeElement($suiviProgression)) {
            if ($suiviProgression->getSession() === $this) {
                $suiviProgression->setSession(null);
            }
        }
        return $this;
    }

    public function canAddSuivi(): bool
    {
        return $this->statut === self::STATUTS[2]; // 'TERMINEE'
    }

    public function getTelephoneResponsable(): ?string
    {
        return $this->telephoneResponsable;
    }

    public function setTelephoneResponsable(?string $telephoneResponsable): static
    {
        $this->telephoneResponsable = $telephoneResponsable;
        return $this;
    }

    public function getCalendarEventId(): ?string
    {
        return $this->calendarEventId;
    }

    public function setCalendarEventId(?string $calendarEventId): static
    {
        $this->calendarEventId = $calendarEventId;
        return $this;
    }

    public function getNomResponsable(): ?string
    {
        return $this->nomResponsable;
    }

    public function setNomResponsable(?string $nomResponsable): static
    {
        $this->nomResponsable = $nomResponsable;
        return $this;
    }

    public function getParentEmail(): ?string
    {
        return $this->parentEmail;
    }

    public function setParentEmail(?string $parentEmail): static
    {
        $this->parentEmail = $parentEmail;
        return $this;
    }

    public function getMeetLink(): ?string { return $this->meetLink; }
    public function setMeetLink(?string $meetLink): static { $this->meetLink = $meetLink; return $this; }

    public function getMeetSecurityCode(): ?string { return $this->meetSecurityCode; }
    public function setMeetSecurityCode(?string $code): static { $this->meetSecurityCode = $code; return $this; }

    public function isFavori(): bool { return $this->favori; }
    public function setFavori(bool $favori): static { $this->favori = $favori; return $this; }

    public function getPointsPresence(): int { return $this->pointsPresence; }
    public function setPointsPresence(int $pts): static { $this->pointsPresence = $pts; return $this; }

    public function getQrToken(): ?string { return $this->qrToken; }
    public function setQrToken(?string $qrToken): static { $this->qrToken = $qrToken; return $this; }

    public function getPresenceConfirmeeAt(): ?\DateTimeInterface { return $this->presenceConfirmeeAt; }
    public function setPresenceConfirmeeAt(?\DateTimeInterface $dt): static { $this->presenceConfirmeeAt = $dt; return $this; }

    public function isPresenceConfirmee(): bool { return $this->presenceConfirmeeAt !== null; }

    public function getTitle(): ?string
    {
        $type = $this->type ?? 'Session';
        $date = $this->dateHeure ? $this->dateHeure->format('d/m/Y H:i') : '';
        return trim("$type - $date");
    }

    public function getFeedbackTherapeute(): ?string { return $this->feedbackTherapeute; }
    public function setFeedbackTherapeute(?string $feedback): static { $this->feedbackTherapeute = $feedback; return $this; }

    public function getFeedbackAt(): ?\DateTimeInterface { return $this->feedbackAt; }
    public function setFeedbackAt(?\DateTimeInterface $dt): static { $this->feedbackAt = $dt; return $this; }

    public function hasFeedback(): bool { return $this->feedbackTherapeute !== null; }
}