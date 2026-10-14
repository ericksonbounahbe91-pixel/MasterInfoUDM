<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private array $perf = [];

    /** Exécute une requête et mesure son temps (analyse de performance). */
    private function mesurer(string $label, \Closure $fn)
    {
        $debut = microtime(true);
        $resultat = $fn();
        $this->perf[$label] = round((microtime(true) - $debut) * 1000, 2);

        return $resultat;
    }

    /** Requête de base : ligne de commande + produit + commande. */
    private function lignes()
    {
        return DB::table('ligneCommande')
            ->join('Produit', 'Produit.idProd', '=', 'ligneCommande.idProd')
            ->join('Commande', 'Commande.idCmd', '=', 'ligneCommande.idCmd');
    }

    public function index()
    {
        $debutTotal = microtime(true);
        $montant = 'ligneCommande.qteCmd * Produit.prixUProd';

        $nbClients   = $this->mesurer('Nombre de clients',   fn () => DB::table('Client')->count());
        $nbCommandes = $this->mesurer('Nombre de commandes', fn () => DB::table('Commande')->count());
        $nbProduits  = $this->mesurer('Nombre de produits',  fn () => DB::table('Produit')->count());

        $chiffreAffaires = $this->mesurer('Chiffre d\'affaires total', fn () =>
            (float) $this->lignes()->sum(DB::raw($montant)));

        $valeurStock = $this->mesurer('Valeur du stock', fn () =>
            (float) DB::table('Produit')->sum(DB::raw('qteStock * prixUProd')));

        $topProduits = $this->mesurer('Top 5 produits', fn () =>
            $this->lignes()
                ->groupBy('Produit.idProd', 'Produit.designProd')
                ->selectRaw("Produit.designProd, SUM(ligneCommande.qteCmd) AS qte, SUM($montant) AS ca")
                ->orderByDesc('qte')->limit(5)->get());

        $caParMois = $this->mesurer('CA par mois', fn () =>
            $this->lignes()
                ->selectRaw("DATE_FORMAT(Commande.dateCmd, '%Y-%m') AS mois, SUM($montant) AS ca")
                ->groupBy('mois')->orderBy('mois')->get());

        $caParDepartement = $this->mesurer('CA par département', fn () =>
            $this->lignes()
                ->join('Client', 'Client.idCli', '=', 'Commande.idCli')
                ->join('Region', 'Region.idRegion', '=', 'Client.idRegion')
                ->join('Departement', 'Departement.idDep', '=', 'Region.idDep')
                ->groupBy('Departement.idDep', 'Departement.nomDep')
                ->selectRaw("Departement.nomDep, SUM($montant) AS ca")
                ->orderByDesc('ca')->get());

        $topClients = $this->mesurer('Top 5 clients', fn () =>
            $this->lignes()
                ->join('Client', 'Client.idCli', '=', 'Commande.idCli')
                ->groupBy('Client.idCli', 'Client.nomCli', 'Client.prenomCli')
                ->selectRaw("Client.nomCli, Client.prenomCli, COUNT(DISTINCT Commande.idCmd) AS nb_cmd, SUM($montant) AS ca")
                ->orderByDesc('ca')->limit(5)->get());

        $stockFaible = $this->mesurer('Produits en stock faible', fn () =>
            DB::table('Produit')->where('qteStock', '<=', 5)->orderBy('qteStock')->limit(10)->get());

        $panierMoyen = $nbCommandes > 0 ? $chiffreAffaires / $nbCommandes : 0;
        $perf = $this->perf;
        $tempsTotal = round((microtime(true) - $debutTotal) * 1000, 2);

        return view('dashboard', compact(
            'nbClients', 'nbCommandes', 'nbProduits', 'chiffreAffaires', 'valeurStock', 'panierMoyen',
            'topProduits', 'caParMois', 'caParDepartement', 'topClients', 'stockFaible', 'perf', 'tempsTotal'
        ));
    }
}
