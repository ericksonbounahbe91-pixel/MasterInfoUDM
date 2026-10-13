<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Base commune : lignes de commande jointes aux produits et aux commandes
    private function lignes()
    {
        return DB::table('ligne_commandes as l')
            ->join('produits as p', 'p.idProd', '=', 'l.idProd')
            ->join('commandes as c', 'c.idCmd', '=', 'l.idCmd');
    }

    public function index()
    {
        // --- KPI ---
        $caTotal = (float) $this->lignes()->selectRaw('COALESCE(SUM(l.qteCmd * p.prixUProd), 0) as ca')->value('ca');
        $nbCommandes = Commande::count();
        $qteVendue = (int) DB::table('ligne_commandes')->sum('qteCmd');
        $panierMoyen = $nbCommandes ? $caTotal / $nbCommandes : 0;

        // --- Top 5 produits par chiffre d'affaires ---
        $topProduits = $this->lignes()
            ->selectRaw('p.designProd as label, SUM(l.qteCmd) as qte, SUM(l.qteCmd * p.prixUProd) as ca')
            ->groupBy('p.idProd', 'p.designProd')
            ->orderByDesc('ca')->limit(5)->get();

        // --- CA par région ---
        $parRegion = $this->lignes()
            ->join('clients as cl', 'cl.idCli', '=', 'c.idCli')
            ->join('regions as r', 'r.idRegion', '=', 'cl.idRegion')
            ->selectRaw('r.nomRegion as label, SUM(l.qteCmd * p.prixUProd) as ca')
            ->groupBy('r.idRegion', 'r.nomRegion')
            ->orderByDesc('ca')->get();

        // --- Évolution mensuelle (MySQL : DATE_FORMAT) ---
        $parMois = $this->lignes()
            ->selectRaw("DATE_FORMAT(c.dateCmd, '%Y-%m') as label, SUM(l.qteCmd * p.prixUProd) as ca")
            ->groupByRaw("DATE_FORMAT(c.dateCmd, '%Y-%m')")
            ->orderBy('label')->get();

        // --- Alertes de stock faible ---
        $stockFaible = Produit::where('qteStock', '<', 10)->orderBy('qteStock')->get();

        // --- Analyse de performance : N+1 vs eager loading vs agrégat SQL ---
        $perf = [
            $this->mesurer('Naïf (N+1, lazy loading)', function () {
                foreach (Commande::all() as $c) {
                    $c->client->nomCli;
                    foreach ($c->produits as $p) {
                        $p->pivot->qteCmd * $p->prixUProd;
                    }
                }
            }),
            $this->mesurer('Optimisé (with: eager loading)', function () {
                foreach (Commande::with(['client', 'produits'])->get() as $c) {
                    $c->client->nomCli;
                    $c->total;
                }
            }),
            $this->mesurer('Agrégat SQL (1 requête GROUP BY)', function () {
                $this->lignes()
                    ->join('clients as cl', 'cl.idCli', '=', 'c.idCli')
                    ->selectRaw('c.idCmd, cl.nomCli, SUM(l.qteCmd * p.prixUProd) as total')
                    ->groupBy('c.idCmd', 'cl.nomCli')->get();
            }),
        ];

        return view('dashboard', compact(
            'caTotal', 'nbCommandes', 'qteVendue', 'panierMoyen',
            'topProduits', 'parRegion', 'parMois', 'stockFaible', 'perf'
        ));
    }

    private function mesurer(string $nom, \Closure $fn): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $debut = microtime(true);
        $fn();
        $ms = round((microtime(true) - $debut) * 1000, 1);
        $requetes = count(DB::getQueryLog());
        DB::disableQueryLog();

        return ['nom' => $nom, 'requetes' => $requetes, 'ms' => $ms];
    }
}
