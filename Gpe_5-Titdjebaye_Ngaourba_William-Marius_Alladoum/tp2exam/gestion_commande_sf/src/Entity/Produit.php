<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'cmd_produit')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, nullable: false)]
    #[Assert\NotBlank(message: 'Saisissez une désignation.')]
    #[Assert\Length(max: 150)]
    private ?string $designation = null;

    #[ORM\Column(nullable: false)]
    #[Assert\NotNull(message: 'Saisissez le stock.')]
    #[Assert\PositiveOrZero(message: 'Le stock doit être supérieur ou égal à 0.')]
    private ?int $stock = 0;

    // Prix en FCFA (entier : le franc CFA n'a pas de centimes)
    #[ORM\Column(nullable: false)]
    #[Assert\NotNull(message: 'Saisissez le prix unitaire.')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être supérieur ou égal à 0.')]
    private ?int $prixUnitaire = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    public function getId(): ?int { return $this->id; }

    public function getDesignation(): ?string { return $this->designation; }
    public function setDesignation(?string $designation): static { $this->designation = $designation; return $this; }

    public function getStock(): int { return $this->stock ?? 0; }
    public function setStock(?int $stock): static { $this->stock = $stock; return $this; }

    public function getPrixUnitaire(): int { return $this->prixUnitaire ?? 0; }
    public function setPrixUnitaire(?int $prixUnitaire): static { $this->prixUnitaire = $prixUnitaire; return $this; }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }
}
