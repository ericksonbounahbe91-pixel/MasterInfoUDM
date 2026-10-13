<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Mesure de performance : on note l'heure de départ et on active le journal des requêtes SQL
        $debut = microtime(true);
        DB::enableQueryLog();

        // Montant d'une ligne de commande = quantité commandée × prix unitaire
        $montant = 'lc.qteCmd * p.prixUProd';

        // Base commune : ligneCommande jointe à produit (une fonction pour repartir d'une requête neuve à chaque fois)
        $lignes = fn () => DB::table('ligneCommande as lc')
            ->join('produit as p', 'p.idProd', '=', 'lc.idProd');

        // --- Indicateurs clés ---
        $nbCommandes = Commande::count();
        $nbClients   = Client::count();
        $caTotal     = (float) $lignes()->selectRaw("COALESCE(SUM($montant), 0) as total")->value('total');
        $panierMoyen = $nbCommandes > 0 ? $caTotal / $nbCommandes : 0;

        // --- CA par mois ---
        $parMois = $lignes()
            ->join('commande as c', 'c.idCmd', '=', 'lc.idCmd')
            ->selectRaw("DATE_FORMAT(c.dateCmd, '%Y-%m') as mois, SUM($montant) as ca")
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // --- 5 produits les plus vendus (en quantité) ---
        $topProduits = $lignes()
            ->selectRaw('p.designProd, SUM(lc.qteCmd) as qte')
            ->groupBy('p.idProd', 'p.designProd')
            ->orderByDesc('qte')
            ->limit(5)
            ->get();

        // --- CA par département (ligne → commande → client → région → département) ---
        $parDepartement = $lignes()
            ->join('commande as c', 'c.idCmd', '=', 'lc.idCmd')
            ->join('client as cl', 'cl.idCli', '=', 'c.idCli')
            ->join('region as r', 'r.idRegion', '=', 'cl.idRegion')
            ->join('departement as d', 'd.idDep', '=', 'r.idDep')
            ->selectRaw("d.nomDep, SUM($montant) as ca")
            ->groupBy('d.idDep', 'd.nomDep')
            ->orderByDesc('ca')
            ->get();

        // --- 5 meilleurs clients (par CA) ---
        $topClients = $lignes()
            ->join('commande as c', 'c.idCmd', '=', 'lc.idCmd')
            ->join('client as cl', 'cl.idCli', '=', 'c.idCli')
            ->selectRaw("CONCAT(cl.prenomCli, ' ', cl.nomCli) as client, SUM($montant) as ca")
            ->groupBy('cl.idCli', 'cl.prenomCli', 'cl.nomCli')
            ->orderByDesc('ca')
            ->limit(5)
            ->get();

        // --- Produits à stock faible ---
        $stockFaible = Produit::where('qteStock', '<', 20)->orderBy('qteStock')->get();

        // Données des graphiques, regroupées dans un tableau transmis au JavaScript
        $charts = [
            'mois' => [
                'labels' => $parMois->pluck('mois')->all(),
                'data'   => $parMois->pluck('ca')->map(fn ($v) => (float) $v)->all(),
            ],
            'produits' => [
                'labels' => $topProduits->pluck('designProd')->all(),
                'data'   => $topProduits->pluck('qte')->map(fn ($v) => (int) $v)->all(),
            ],
            'departements' => [
                'labels' => $parDepartement->pluck('nomDep')->all(),
                'data'   => $parDepartement->pluck('ca')->map(fn ($v) => (float) $v)->all(),
            ],
        ];

        // Résultat de la mesure de performance
        $nbRequetes = count(DB::getQueryLog());
        $dureeMs    = round((microtime(true) - $debut) * 1000);

        return view('dashboard', compact(
            'nbCommandes', 'nbClients', 'caTotal', 'panierMoyen',
            'topClients', 'stockFaible', 'charts', 'nbRequetes', 'dureeMs'
        ));
    }
}