<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Service\StatistiquesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function index(
        Request $request,
        StatistiquesService $stats,
        ProduitRepository $produits,
        #[Autowire('%seuil_stock%')] int $seuilStock,
    ): Response {
        // Période : par défaut, de la première à la dernière commande
        [$min, $max] = $stats->bornes();
        $du = $this->dateValide($request->query->get('du'), $min ?? date('Y-01-01'));
        $au = $this->dateValide($request->query->get('au'), $max ?? date('Y-m-d'));
        if ($du > $au) {
            [$du, $au] = [$au, $du];
        }

        $indicateurs = $stats->indicateurs($du, $au);
        $topProduits = $stats->topProduits($du, $au);
        $partTop = $indicateurs['ca'] > 0 ? (int) round(array_sum(array_column($topProduits, 'ca')) / $indicateurs['ca'] * 100) : 0;

        return $this->render('dashboard/index.html.twig', [
            'du' => $du,
            'au' => $au,
            'k' => $indicateurs,
            'mensuel' => $stats->parMois($du, $au),
            'topProduits' => $topProduits,
            'partTop' => $partTop,
            'parDepartement' => $stats->parDepartement($du, $au),
            'topClients' => $stats->topClients($du, $au),
            'stockBas' => $produits->stockBas($seuilStock),
        ]);
    }

    private function dateValide(?string $valeur, string $defaut): string
    {
        $d = $valeur ? \DateTimeImmutable::createFromFormat('!Y-m-d', $valeur) : false;
        return ($d && $d->format('Y-m-d') === $valeur) ? $valeur : $defaut;
    }
}
