<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "activite")]
class Activite
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
#[ORM\Column(nullable: true)]
private ?string $typeActivite = null;

#[ORM\Column(nullable: true)]
private ?string $image1 = null;

#[ORM\Column(nullable: true)]
private ?string $image2 = null;

#[ORM\Column(nullable: true)]
private ?string $image3 = null;

#[ORM\Column(nullable: true)]
private ?string $image4 = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $question = null;

    #[ORM\Column(nullable: true)]
    private ?string $choix1 = null;
#[ORM\Column(nullable: true)]
private ?int $niveau = null;
    #[ORM\Column(nullable: true)]
    private ?string $choix2 = null;

    #[ORM\Column(nullable: true)]
    private ?string $choix3 = null;

    #[ORM\Column(nullable: true)]
    private ?string $choix4 = null;
public function getImage1(): ?string { return $this->image1; }
public function setImage1(?string $image): self { $this->image1 = $image; return $this; }

public function getImage2(): ?string { return $this->image2; }
public function setImage2(?string $image): self { $this->image2 = $image; return $this; }

public function getImage3(): ?string { return $this->image3; }
public function setImage3(?string $image): self { $this->image3 = $image; return $this; }

public function getImage4(): ?string { return $this->image4; }
public function setImage4(?string $image): self { $this->image4 = $image; return $this; }
    #[ORM\Column(nullable: true)]
    private ?string $bonneReponse = null;
#[ORM\Column(name: "image_url", nullable: true)]
private ?string $imageUrl = null;
#[ORM\Column(nullable: true)]
private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: JeuEducatif::class, inversedBy: 'activites')]
    #[ORM\JoinColumn(name: "jeu_id", referencedColumnName: "id", nullable: false)]
    private ?JeuEducatif $jeu = null;

    // ===== GETTERS / SETTERS =====
public function getImageUrl(): ?string
{
    return $this->imageUrl;
}

public function setImageUrl(?string $imageUrl): self
{
    $this->imageUrl = $imageUrl;
    return $this;
}
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getChoix1(): ?string
    {
        return $this->choix1;
    }

    public function setChoix1(?string $choix1): self
    {
        $this->choix1 = $choix1;
        return $this;
    }

    public function getChoix2(): ?string
    {
        return $this->choix2;
    }

    public function setChoix2(?string $choix2): self
    {
        $this->choix2 = $choix2;
        return $this;
    }

    public function getChoix3(): ?string
    {
        return $this->choix3;
    }

    public function setChoix3(?string $choix3): self
    {
        $this->choix3 = $choix3;
        return $this;
    }

    public function getChoix4(): ?string
    {
        return $this->choix4;
    }

    public function setChoix4(?string $choix4): self
    {
        $this->choix4 = $choix4;
        return $this;
    }

    public function getBonneReponse(): ?string
    {
        return $this->bonneReponse;
    }

    public function setBonneReponse(?string $bonneReponse): self
    {
        $this->bonneReponse = $bonneReponse;
        return $this;
    }

    public function getJeu(): ?JeuEducatif
    {
        return $this->jeu;
    }

    public function setJeu(?JeuEducatif $jeu): self
    {
        $this->jeu = $jeu;
        return $this;
    }
    public function getImage(): ?string
{
    return $this->image;
}

public function setImage(?string $image): self
{
    $this->image = $image;
    return $this;
}
public function getNiveau(): ?int { return $this->niveau; }

public function setNiveau(?int $niveau): self {
    $this->niveau = $niveau;
    return $this;
}
public function getTypeActivite(): ?string
{
    return $this->typeActivite;
}

public function setTypeActivite(?string $typeActivite): self
{
    $this->typeActivite = $typeActivite;
    return $this;
}
}