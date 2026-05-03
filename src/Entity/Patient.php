<?php
// src/Entity/Patient.php
namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int|null $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    // Métriques pour l'analyse
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $derniereConnexion = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $nombreSessions = 0;

    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0])]
    private float $tauxCompletion = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $joursInactivite = 0;

    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0])]
    private float $frequenceHebdo = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $exercicesAbandonnes = 0;

    #[ORM\Column(length: 20, options: ['default' => 'NORMAL'])]
    private string $statut = 'NORMAL';

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $scoreRisque = null;

    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Session::class)]
    private Collection $sessions;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    // Getters & Setters
    public function getId(): int|null { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getDerniereConnexion(): ?\DateTimeInterface { return $this->derniereConnexion; }
    public function setDerniereConnexion(?\DateTimeInterface $date): static { $this->derniereConnexion = $date; return $this; }
    public function getNombreSessions(): int { return $this->nombreSessions; }
    public function setNombreSessions(int $n): static { $this->nombreSessions = $n; return $this; }
    public function getTauxCompletion(): float { return $this->tauxCompletion; }
    public function setTauxCompletion(float $t): static { $this->tauxCompletion = $t; return $this; }
    public function getJoursInactivite(): int { return $this->joursInactivite; }
    public function setJoursInactivite(int $j): static { $this->joursInactivite = $j; return $this; }
    public function getFrequenceHebdo(): float { return $this->frequenceHebdo; }
    public function setFrequenceHebdo(float $f): static { $this->frequenceHebdo = $f; return $this; }
    public function getExercicesAbandonnes(): int { return $this->exercicesAbandonnes; }
    public function setExercicesAbandonnes(int $e): static { $this->exercicesAbandonnes = $e; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $s): static { $this->statut = $s; return $this; }
    public function getScoreRisque(): ?float { return $this->scoreRisque; }
    public function setScoreRisque(?float $s): static { $this->scoreRisque = $s; return $this; }
    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection { return $this->sessions; }
}