<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateHeure = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La durée est obligatoire")]
    #[Assert\Positive(message: "La durée doit être positive")]
    #[Assert\LessThanOrEqual(
        value: 300,
        message: "La durée ne peut pas dépasser 5 heures (300 minutes)"
    )]
    private ?int $duree = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le type de thérapie est obligatoire")]
    #[Assert\Choice(
        choices: ['ABA', 'PECS', 'Orthophonie', 'Psychomotricité', 'Ergothérapie'],
        message: "Type de thérapie invalide"
    )]
    private ?string $type = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ['PLANIFIEE', 'EN_COURS', 'TERMINEE', 'ANNULEE'],
        message: "Statut invalide"
    )]
    private ?string $statut = 'PLANIFIEE';

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le niveau d'agitation est obligatoire")]
    #[Assert\Choice(
        choices: ['CALME', 'AGITE', 'CRISE'],
        message: "Niveau d'agitation invalide"
    )]
    private ?string $niveauAgitation = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: "Max 100 caractères")]
    private ?string $techniqueUtilisee = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "Max 1000 caractères")]
    private ?string $objectifSeance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: "Max 2000 caractères")]
    private ?string $notes = null;

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: SuiviProgression::class, orphanRemoval: true)]
    private Collection $suiviProgressions;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->suiviProgressions = new ArrayCollection();
        $this->dateHeure = new \DateTime(); // ✅ Initialisation ici
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

    // ============ GETTERS & SETTERS ============

    public function getId(): ?int { return $this->id; }

    public function getDateHeure(): ?\DateTimeInterface { return $this->dateHeure; }
    
    // ✅ PAS DE nullable ici - compatible avec Doctrine Proxy
    public function setDateHeure(\DateTimeInterface $dateHeure): static { 
        $this->dateHeure = $dateHeure; 
        return $this; 
    }

    public function getDuree(): ?int { return $this->duree; }
    public function setDuree(int $duree): static { $this->duree = $duree; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getNiveauAgitation(): ?string { return $this->niveauAgitation; }
    public function setNiveauAgitation(string $niveauAgitation): static { $this->niveauAgitation = $niveauAgitation; return $this; }

    public function getTechniqueUtilisee(): ?string { return $this->techniqueUtilisee; }
    public function setTechniqueUtilisee(?string $techniqueUtilisee): static { $this->techniqueUtilisee = $techniqueUtilisee; return $this; }

    public function getObjectifSeance(): ?string { return $this->objectifSeance; }
    public function setObjectifSeance(?string $objectifSeance): static { $this->objectifSeance = $objectifSeance; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }

    /**
     * @return Collection<int, SuiviProgression>
     */
    public function getSuiviProgressions(): Collection { return $this->suiviProgressions; }

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
        return $this->statut === 'TERMINEE';
    }
}