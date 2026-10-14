<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/** Ajoute le filtre {{ montant|fcfa }} utilisable dans tous les templates. */
class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [new TwigFilter('fcfa', [$this, 'fcfa'])];
    }

    public function fcfa(int|float|string|null $montant): string
    {
        return number_format((float) $montant, 0, ',', ' ') . ' FCFA';
    }
}
