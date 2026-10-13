<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    private function lignes()
    {
        return DB::table('ligne_commandes as l')
            ->join('produits as p', 'p.idProd', '=', 'l.idProd')
            ->join('commandes as c', 'c.idCmd', '=', 'l.idCmd');
    }

    private function fmt($rows): array
    {
        return $rows->map(fn ($r) => ['label' => $r->label, 'ca' => (float) $r->ca])->values()->all();
    }

    public function index()
    {
        $ca = (float) $this->lignes()->selectRaw('COALESCE(SUM(l.qteCmd * p.prixUProd), 0) as ca')->value('ca');
        $nb = Commande::count();

        $top = $this->lignes()
            ->selectRaw('p.designProd as label, SUM(l.qteCmd * p.prixUProd) as ca')
            ->groupBy('p.idProd', 'p.designProd')->orderByDesc('ca')->limit(5)->get();

        $region = $this->lignes()
            ->join('clients as cl', 'cl.idCli', '=', 'c.idCli')
            ->join('regions as r', 'r.idRegion', '=', 'cl.idRegion')
            ->selectRaw('r.nomRegion as label, SUM(l.qteCmd * p.prixUProd) as ca')
            ->groupBy('r.idRegion', 'r.nomRegion')->orderByDesc('ca')->get();

        $mois = $this->lignes()
            ->selectRaw("DATE_FORMAT(c.dateCmd, '%Y-%m') as label, SUM(l.qteCmd * p.prixUProd) as ca")
            ->groupByRaw("DATE_FORMAT(c.dateCmd, '%Y-%m')")->orderBy('label')->get();

        $stock = Produit::where('qteStock', '<', 10)->orderBy('qteStock')->get()
            ->map(fn ($p) => ['idProd' => (int) $p->idProd, 'designProd' => $p->designProd, 'qteStock' => (int) $p->qteStock])
            ->values();

        return response()->json([
            'kpi' => [
                'ca'           => $ca,
                'commandes'    => $nb,
                'panier_moyen' => $nb ? $ca / $nb : 0,
                'unites'       => (int) DB::table('ligne_commandes')->sum('qteCmd'),
            ],
            'top_produits' => $this->fmt($top),
            'par_region'   => $this->fmt($region),
            'par_mois'     => $this->fmt($mois),
            'stock_faible' => $stock,
        ]);
    }
}
