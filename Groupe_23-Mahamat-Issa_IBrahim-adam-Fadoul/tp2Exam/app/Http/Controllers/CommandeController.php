<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommandeController extends Controller
{
    // Consigne 2 : liste des commandes
    public function index()
    {
        $commandes = Commande::with(['client', 'produits'])->orderByDesc('dateCmd')->paginate(10);
        return view('commandes.index', compact('commandes'));
    }

    // Consigne 1 : formulaire d'enregistrement des produits d'une commande
    public function create()
    {
        return view('commandes.create', [
            'clients'  => Client::orderBy('nomCli')->get(),
            'produits' => Produit::where('qteStock', '>', 0)->orderBy('designProd')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'idCli'                => 'required|exists:clients,idCli',
            'dateCmd'              => 'required|date',
            'lignes'               => 'required|array|min:1',
            'lignes.*.idProd'      => 'required|exists:produits,idProd|distinct',
            'lignes.*.qteCmd'      => 'required|integer|min:1',
        ], [
            'lignes.required'        => 'Ajoutez au moins un produit.',
            'lignes.*.idProd.distinct' => 'Un produit ne peut apparaître qu\'une seule fois dans la commande.',
        ]);

        $commande = DB::transaction(function () use ($data) {
            $commande = Commande::create([
                'idCli'   => $data['idCli'],
                'dateCmd' => $data['dateCmd'],
            ]);

            foreach ($data['lignes'] as $ligne) {
                $produit = Produit::lockForUpdate()->findOrFail($ligne['idProd']);

                if ($produit->qteStock < $ligne['qteCmd']) {
                    // L'exception annule toute la transaction
                    throw ValidationException::withMessages([
                        'lignes' => "Stock insuffisant pour « {$produit->designProd} » (disponible : {$produit->qteStock}).",
                    ]);
                }

                $commande->produits()->attach($produit->idProd, ['qteCmd' => $ligne['qteCmd']]);
                $produit->decrement('qteStock', $ligne['qteCmd']);
            }

            return $commande;
        });

        return redirect()->route('commandes.show', $commande)->with('ok', 'Commande enregistrée.');
    }

    public function show(Commande $commande)
    {
        $commande->load('client.region.departement', 'produits');
        return view('commandes.show', compact('commande'));
    }

    // Consigne 4 : supprimer un produit dans une commande (le stock est restitué)
    public function retirerProduit(Commande $commande, Produit $produit)
    {
        DB::transaction(function () use ($commande, $produit) {
            $ligne = $commande->produits()->where('produits.idProd', $produit->idProd)->firstOrFail();
            $produit->increment('qteStock', $ligne->pivot->qteCmd);
            $commande->produits()->detach($produit->idProd);
        });

        return back()->with('ok', 'Produit retiré de la commande.');
    }
}
