<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ligne d'une commande : un produit + une quantité.
 * Règle métier : un même produit ne peut apparaître qu'une fois par commande
 * (contrainte d'unicité sur le couple commande / produit).
 */
#[ORM\Entity]
#[ORM\Table(name: 'cmd_ligne_commande')]
#[ORM\UniqueConstraint(name: 'uniq_ligne_commande_produit', columns: ['commande_id', 'produit_id'])]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $commande = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column]
    private int $quantite = 0;

    public function getId(): ?int { return $this->id; }

    public function getCommande(): ?Commande { return $this->commande; }
    public function setCommande(?Commande $commande): static { $this->commande = $commande; return $this; }

    public function getProduit(): ?Produit { return $this->produit; }
    public function setProduit(?Produit $produit): static { $this->produit = $produit; return $this; }

    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getSousTotal(): int
    {
        return $this->quantite * ($this->produit?->getPrixUnitaire() ?? 0);
    }
}
