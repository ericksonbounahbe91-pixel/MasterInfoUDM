<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommandeApiController extends Controller
{
    public function index()
    {
        return Commande::with(['client', 'produits'])
            ->orderByDesc('dateCmd')->orderByDesc('idCmd')->limit(200)->get()
            ->map(fn ($c) => $this->fmt($c))->values();
    }

    public function show(Commande $commande)
    {
        $commande->load('client.region.departement', 'produits');
        return $this->fmt($commande) + [
            'region'      => $commande->client->region->nomRegion,
            'departement' => $commande->client->region->departement->nomDep,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'idCli'           => 'required|exists:clients,idCli',
            'dateCmd'         => 'required|date',
            'lignes'          => 'required|array|min:1',
            'lignes.*.idProd' => 'required|exists:produits,idProd|distinct',
            'lignes.*.qteCmd' => 'required|integer|min:1',
        ], [
            'lignes.required'          => 'Ajoutez au moins un produit.',
            'lignes.*.idProd.distinct' => 'Un produit ne peut apparaître qu\'une seule fois dans la commande.',
        ]);

        $commande = DB::transaction(function () use ($data) {
            $commande = Commande::create(['idCli' => $data['idCli'], 'dateCmd' => $data['dateCmd']]);

            foreach ($data['lignes'] as $ligne) {
                $produit = Produit::lockForUpdate()->findOrFail($ligne['idProd']);

                if ($produit->qteStock < $ligne['qteCmd']) {
                    throw ValidationException::withMessages([
                        'lignes' => "Stock insuffisant pour « {$produit->designProd} » (disponible : {$produit->qteStock}).",
                    ]);
                }

                $commande->produits()->attach($produit->idProd, ['qteCmd' => $ligne['qteCmd']]);
                $produit->decrement('qteStock', $ligne['qteCmd']);
            }

            return $commande;
        });

        $commande->load('client', 'produits');
        return response()->json($this->fmt($commande), 201);
    }

    // Supprime un produit d'une commande et restitue le stock
    public function retirerProduit(Commande $commande, Produit $produit)
    {
        DB::transaction(function () use ($commande, $produit) {
            $ligne = $commande->produits()->where('produits.idProd', $produit->idProd)->firstOrFail();
            $produit->increment('qteStock', $ligne->pivot->qteCmd);
            $commande->produits()->detach($produit->idProd);
        });

        return response()->json(['message' => 'Produit retiré de la commande.']);
    }

    private function fmt(Commande $c): array
    {
        $lignes = $c->produits->map(fn ($p) => [
            'idProd'     => (int) $p->idProd,
            'designProd' => $p->designProd,
            'prixUProd'  => (float) $p->prixUProd,
            'qteCmd'     => (int) $p->pivot->qteCmd,
            'sousTotal'  => (float) ($p->pivot->qteCmd * $p->prixUProd),
        ])->values();

        return [
            'idCmd'      => (int) $c->idCmd,
            'dateCmd'    => $c->dateCmd->toDateString(),
            'client'     => trim($c->client->prenomCli . ' ' . $c->client->nomCli),
            'nbProduits' => $lignes->count(),
            'total'      => (float) $lignes->sum('sousTotal'),
            'lignes'     => $lignes,
        ];
    }
}
