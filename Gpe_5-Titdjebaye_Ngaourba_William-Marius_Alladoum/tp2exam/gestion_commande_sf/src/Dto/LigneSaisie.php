<?php

namespace App\Dto;

use App\Entity\Produit;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** Une ligne saisie dans le formulaire « ajouter des produits » (produit + quantité). */
class LigneSaisie
{
    public ?Produit $produit = null;

    #[Assert\Positive(message: 'La quantité doit être supérieure à 0.')]
    public ?int $quantite = null;

    #[Assert\Callback]
    public function valider(ExecutionContextInterface $context): void
    {
        if ($this->produit !== null && $this->quantite === null) {
            $context->buildViolation('Indiquez une quantité.')->atPath('quantite')->addViolation();
        }
        if ($this->produit === null && $this->quantite !== null) {
            $context->buildViolation('Choisissez un produit.')->atPath('produit')->addViolation();
        }
    }
}
