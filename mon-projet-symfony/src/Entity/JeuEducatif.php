<?php

namespace App\Entity;
use Symfony\Component\Form\Extension\Core\Type\FileType; // IMPORTANT
use App\Entity\Activite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "jeu_educatif")]
class JeuEducatif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
#[ORM\Column(type: 'integer')]
private $likes = 0;


    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $niveau = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    
#[ORM\Column(nullable: true)]
private ?string $image = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $deleted = null;

    #[ORM\OneToMany(mappedBy: 'jeu', targetEntity: Activite::class)]
    private Collection $activites;

    public function __construct()
    {
        $this->activites = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(?string $niveau): self { $this->niveau = $niveau; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

public function getLikes(): int
{
    return $this->likes;
}

public function setLikes(int $likes): self
{
    $this->likes = $likes;
    return $this;
}

public function addLike(): self
{
    $this->likes++;
    return $this;
}
public function getImage(): ?string { return $this->image; }
public function setImage(?string $image): self { $this->image = $image; return $this; }

    public function getDeleted(): ?bool { return $this->deleted; }
    public function setDeleted(?bool $deleted): self { $this->deleted = $deleted; return $this; }

    public function getActivites(): Collection
    {
        return $this->activites;
    }
    public function getLevelNumber(): int
{
    return match($this->niveau) {
        'facile' => 2,
        'moyen' => 3,
        'difficile' => 4,
        default => 2
    };
}
}