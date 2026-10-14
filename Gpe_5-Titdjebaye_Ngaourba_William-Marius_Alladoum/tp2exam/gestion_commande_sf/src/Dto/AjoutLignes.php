<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/** Objet porté par le formulaire : une liste de lignes à enregistrer d'un coup. */
class AjoutLignes
{
    /** @var LigneSaisie[] */
    #[Assert\Valid]
    public array $lignes = [];

    public function __construct()
    {
        $this->lignes = [new LigneSaisie()];   // une ligne vide affichée au départ
    }
}
