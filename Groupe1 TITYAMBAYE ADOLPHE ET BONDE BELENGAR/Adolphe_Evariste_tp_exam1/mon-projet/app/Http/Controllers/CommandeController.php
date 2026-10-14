<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function index()
    {
        // Une seule requête : le total de chaque commande est calculé en SQL (pas de N+1)
        $commandes = Commande::query()
            ->join('Client', 'Client.idCli', '=', 'Commande.idCli')
            ->leftJoin('ligneCommande', 'ligneCommande.idCmd', '=', 'Commande.idCmd')
            ->leftJoin('Produit', 'Produit.idProd', '=', 'ligneCommande.idProd')
            ->groupBy('Commande.idCmd', 'Commande.dateCmd', 'Commande.idCli', 'Client.nomCli', 'Client.prenomCli')
            ->selectRaw('Commande.idCmd, Commande.dateCmd, Client.nomCli, Client.prenomCli,
                         COUNT(ligneCommande.idProd) AS nb_lignes,
                         COALESCE(SUM(ligneCommande.qteCmd * Produit.prixUProd), 0) AS total')
            ->orderByDesc('Commande.dateCmd')
            ->orderByDesc('Commande.idCmd')
            ->paginate(10);

        return view('commandes.index', compact('commandes'));
    }

    public function create()
    {
        $clients = Client::orderBy('nomCli')->get();

        return view('commandes.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'idCli'   => 'required|exists:Client,idCli',
            'dateCmd' => 'required|date',
        ]);

        $commande = Commande::create($data);

        return redirect()->route('commandes.show', $commande)
            ->with('success', 'Commande créée : ajoutez maintenant ses produits.');
    }

    public function show(Commande $commande)
    {
        $commande->load('client', 'produits');
        $produitsDisponibles = Produit::where('qteStock', '>', 0)->orderBy('designProd')->get();

        return view('commandes.show', compact('commande', 'produitsDisponibles'));
    }

    // Formulaire d'enregistrement d'un produit dans une commande donnée
    public function ajouterLigne(Request $request, Commande $commande)
    {
        $data = $request->validate([
            'idProd' => 'required|exists:Produit,idProd',
            'qteCmd' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($commande, $data) {
                // verrou sur la ligne produit : évite deux commandes simultanées sur le même stock
                $produit = Produit::lockForUpdate()->findOrFail($data['idProd']);

                if ($produit->qteStock < $data['qteCmd']) {
                    throw new \RuntimeException(
                        "Stock insuffisant pour « {$produit->designProd} » (disponible : {$produit->qteStock})."
                    );
                }

                $existante = $commande->produits()->where('Produit.idProd', $produit->idProd)->first();

                if ($existante) {
                    // clé primaire composée (idCmd, idProd) : on cumule la quantité
                    $commande->produits()->updateExistingPivot($produit->idProd, [
                        'qteCmd' => $existante->pivot->qteCmd + $data['qteCmd'],
                    ]);
                } else {
                    $commande->produits()->attach($produit->idProd, ['qteCmd' => $data['qteCmd']]);
                }

                $produit->decrement('qteStock', $data['qteCmd']);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['qteCmd' => $e->getMessage()]);
        }

        return redirect()->route('commandes.show', $commande)->with('success', 'Produit ajouté à la commande.');
    }

    // Suppression d'un produit dans une commande (le stock est remis à jour)
    public function supprimerLigne(Commande $commande, Produit $produit)
    {
        DB::transaction(function () use ($commande, $produit) {
            $ligne = $commande->produits()->where('Produit.idProd', $produit->idProd)->first();
            abort_if(! $ligne, 404);

            Produit::lockForUpdate()->findOrFail($produit->idProd)
                ->increment('qteStock', $ligne->pivot->qteCmd);

            $commande->produits()->detach($produit->idProd);
        });

        return redirect()->route('commandes.show', $commande)->with('success', 'Produit retiré de la commande.');
    }
}
