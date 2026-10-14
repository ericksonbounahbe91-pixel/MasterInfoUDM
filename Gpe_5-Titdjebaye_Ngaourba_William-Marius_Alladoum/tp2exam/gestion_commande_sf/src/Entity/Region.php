<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'cmd_region')]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: false)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    // Une région appartient à un département (clé étrangère departement_id)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Departement $departement = null;

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }

    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }

    public function getDepartement(): ?Departement { return $this->departement; }

    public function setDepartement(?Departement $departement): static { $this->departement = $departement; return $this; }
}
