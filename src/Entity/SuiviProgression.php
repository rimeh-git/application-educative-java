<?php

namespace App\Entity;

use App\Repository\SuiviProgressionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SuiviProgressionRepository::class)]
#[ORM\HasLifecycleCallbacks]  // ✅ AJOUTÉ - pour PrePersist/PreUpdate
class SuiviProgression
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'suivis')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Veuillez sélectionner une session')]
    private ?Session $session = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Veuillez sélectionner un domaine')]
    #[Assert\Choice(
        choices: ['COMMUNICATION', 'SOCIALISATION', 'COMPORTEMENT', 'AUTONOMIE', 'MOTRICITE', 'COGNITION'],
        message: 'Domaine invalide'
    )]
    private ?string $domaine = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull(message: 'Le score avant est obligatoire')]
    #[Assert\Range(
        min: 0,
        max: 10,
        notInRangeMessage: 'Le score doit être entre {{ min }} et {{ max }}'
    )]
    private ?int $scoreAvant = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull(message: 'Le score après est obligatoire')]
    #[Assert\Range(
        min: 0,
        max: 10,
        notInRangeMessage: 'Le score doit être entre {{ min }} et {{ max }}'
    )]
    private ?int $scoreApres = null;

    // ✅ FIX PRINCIPAL - nullable: true dans le mapping DB
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $progression = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'Max {{ limit }} caractères')]
    private ?string $comportementsObserves = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: 'Max {{ limit }} caractères')]
    private ?string $declencheursIdentifies = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1500, maxMessage: 'Max {{ limit }} caractères')]
    private ?string $objectifsRealises = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'Max {{ limit }} caractères')]
    private ?string $recommandationsParent = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date d'évaluation est obligatoire")]
    #[Assert\LessThanOrEqual(value: 'today', message: 'La date ne peut pas être dans le futur')]
    private ?\DateTimeInterface $dateEvaluation = null;

    public function __construct()
    {
        $this->dateEvaluation = new \DateTime();
    }

    // ✅ LIFECYCLE CALLBACKS - calcul automatique avant save ET update
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function calculerProgression(): void
    {
        if ($this->scoreAvant !== null && $this->scoreApres !== null) {
            $this->progression = $this->scoreApres - $this->scoreAvant;
        }
    }

    // ============ GETTERS & SETTERS ============

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;
        return $this;
    }

    public function getDomaine(): ?string
    {
        return $this->domaine;
    }

    public function setDomaine(?string $domaine): static
    {
        $this->domaine = $domaine;
        return $this;
    }

    public function getScoreAvant(): ?int
    {
        return $this->scoreAvant;
    }

    public function setScoreAvant(?int $scoreAvant): static
    {
        $this->scoreAvant = $scoreAvant;
        return $this;
    }

    public function getScoreApres(): ?int
    {
        return $this->scoreApres;
    }

    public function setScoreApres(?int $scoreApres): static
    {
        $this->scoreApres = $scoreApres;
        return $this;
    }

    public function getProgression(): ?int
    {
        return $this->progression;
    }

    public function setProgression(?int $progression): static
    {
        $this->progression = $progression;
        return $this;
    }

    public function getComportementsObserves(): ?string
    {
        return $this->comportementsObserves;
    }

    public function setComportementsObserves(?string $comportementsObserves): static
    {
        $this->comportementsObserves = $comportementsObserves;
        return $this;
    }

    public function getDeclencheursIdentifies(): ?string
    {
        return $this->declencheursIdentifies;
    }

    public function setDeclencheursIdentifies(?string $declencheursIdentifies): static
    {
        $this->declencheursIdentifies = $declencheursIdentifies;
        return $this;
    }

    public function getObjectifsRealises(): ?string
    {
        return $this->objectifsRealises;
    }

    public function setObjectifsRealises(?string $objectifsRealises): static
    {
        $this->objectifsRealises = $objectifsRealises;
        return $this;
    }

    public function getRecommandationsParent(): ?string
    {
        return $this->recommandationsParent;
    }

    public function setRecommandationsParent(?string $recommandationsParent): static
    {
        $this->recommandationsParent = $recommandationsParent;
        return $this;
    }

    public function getDateEvaluation(): ?\DateTimeInterface
    {
        return $this->dateEvaluation;
    }

    public function setDateEvaluation(?\DateTimeInterface $dateEvaluation): static
    {
        $this->dateEvaluation = $dateEvaluation;
        return $this;
    }
}