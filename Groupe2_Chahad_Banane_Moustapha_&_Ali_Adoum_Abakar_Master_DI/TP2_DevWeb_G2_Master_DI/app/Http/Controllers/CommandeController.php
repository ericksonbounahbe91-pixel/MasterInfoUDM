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
    public function index()
    {
        $commandes = Commande::with(['client.region.departement', 'produits'])
                             ->orderByDesc('dateCmd')
                             ->paginate(10);

        return view('commandes.index', compact('commandes'));
    }

    public function create()
    {
        $clients = Client::with('region.departement')->orderBy('nomCli')->get();

        return view('commandes.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'idCli'   => ['required', 'exists:client,idCli'],
            'dateCmd' => ['required', 'date'],
        ], [
            'idCli.required'   => 'Veuillez choisir un client.',
            'idCli.exists'     => "Ce client n'existe pas.",
            'dateCmd.required' => 'La date est obligatoire.',
            'dateCmd.date'     => "La date n'est pas valide.",
        ]);

        $commande = Commande::create($donnees);

        return redirect()
            ->route('commandes.show', $commande)
            ->with('success', 'Commande créée. Ajoutez maintenant ses produits.');
    }

    public function show(Commande $commande)
    {
        $commande->load(['client', 'produits']);

        $idsDejaCommandes = $commande->produits->pluck('idProd');

        $produitsDisponibles = Produit::where('qteStock', '>', 0)
                                      ->whereNotIn('idProd', $idsDejaCommandes)
                                      ->orderBy('designProd')
                                      ->get();

        return view('commandes.show', compact('commande', 'produitsDisponibles'));
    }

    // Formulaire de modification d'une commande (comme clients.edit)
    public function edit(Commande $commande)
    {
        $commande->load('produits');

        $clients = Client::with('region.departement')->orderBy('nomCli')->get();

        // Produits qu'on peut encore ajouter : en stock et pas déjà dans la commande
        $produitsDisponibles = Produit::where('qteStock', '>', 0)
                                      ->whereNotIn('idProd', $commande->produits->pluck('idProd'))
                                      ->orderBy('designProd')
                                      ->get();

        return view('commandes.edit', compact('commande', 'clients', 'produitsDisponibles'));
    }

    // Enregistre tout d'un coup : client, date, quantités, produits retirés, produit ajouté
    public function update(Request $request, Commande $commande)
    {
        $donnees = $request->validate([
            'idCli'          => ['required', 'exists:client,idCli'],
            'dateCmd'        => ['required', 'date'],
            'lignes'         => ['nullable', 'array'],
            'lignes.*'       => ['required', 'integer', 'min:1'],
            'retirer'        => ['nullable', 'array'],
            'nouveau.idProd' => ['nullable', 'exists:produit,idProd'],
            'nouveau.qteCmd' => ['nullable', 'integer', 'min:1', 'required_with:nouveau.idProd'],
        ], [
            'idCli.required'          => 'Veuillez choisir un client.',
            'idCli.exists'            => "Ce client n'existe pas.",
            'dateCmd.required'        => 'La date est obligatoire.',
            'dateCmd.date'            => "La date n'est pas valide.",
            'lignes.*.required'       => 'Chaque quantité est obligatoire.',
            'lignes.*.integer'        => 'Chaque quantité doit être un nombre entier.',
            'lignes.*.min'            => 'Chaque quantité doit être au moins égale à 1.',
            'nouveau.idProd.exists'   => "Ce produit n'existe pas.",
            'nouveau.qteCmd.integer'  => 'La quantité du produit ajouté doit être un nombre entier.',
            'nouveau.qteCmd.min'      => 'La quantité du produit ajouté doit être au moins égale à 1.',
            'nouveau.qteCmd.required_with' => 'Indiquez la quantité du produit ajouté.',
        ]);

        $retirer = array_map('intval', $donnees['retirer'] ?? []);
        $lignes  = $donnees['lignes'] ?? [];

        // Une exception lancée dans la transaction annule TOUT (rien n'est enregistré à moitié)
        DB::transaction(function () use ($donnees, $commande, $retirer, $lignes) {

            // 1. Client et date
            $commande->update([
                'idCli'   => $donnees['idCli'],
                'dateCmd' => $donnees['dateCmd'],
            ]);

            $commande->load('produits');

            // 2. Lignes existantes : retrait ou changement de quantité
            foreach ($commande->produits as $ligne) {
                $id       = (int) $ligne->idProd;
                $ancienne = (int) $ligne->pivot->qteCmd;
                $p        = Produit::lockForUpdate()->find($id);

                if (in_array($id, $retirer, true)) {
                    $commande->produits()->detach($id);
                    $p->increment('qteStock', $ancienne);   // la quantité retourne en stock
                    continue;
                }

                $nouvelle   = (int) ($lignes[$id] ?? $ancienne);
                $difference = $nouvelle - $ancienne;

                if ($difference > $p->qteStock) {
                    $max = $p->qteStock + $ancienne;
                    throw ValidationException::withMessages([
                        'lignes' => "Stock insuffisant pour « {$p->designProd} » : maximum {$max} unité(s).",
                    ]);
                }

                if ($difference > 0) {
                    $commande->produits()->updateExistingPivot($id, ['qteCmd' => $nouvelle]);
                    $p->decrement('qteStock', $difference);
                } elseif ($difference < 0) {
                    $commande->produits()->updateExistingPivot($id, ['qteCmd' => $nouvelle]);
                    $p->increment('qteStock', -$difference);
                }
            }

            // 3. Produit ajouté (facultatif)
            if (! empty($donnees['nouveau']['idProd'])) {
                $idNouveau = (int) $donnees['nouveau']['idProd'];
                $qte       = (int) ($donnees['nouveau']['qteCmd'] ?? 1);
                $p         = Produit::lockForUpdate()->find($idNouveau);

                if ($commande->produits()->wherePivot('idProd', $idNouveau)->exists()) {
                    throw ValidationException::withMessages([
                        'nouveau.idProd' => 'Ce produit figure déjà dans la commande.',
                    ]);
                }

                if ($qte > $p->qteStock) {
                    throw ValidationException::withMessages([
                        'nouveau.qteCmd' => "Stock insuffisant : il ne reste que {$p->qteStock} unité(s) de « {$p->designProd} ».",
                    ]);
                }

                $commande->produits()->attach($idNouveau, ['qteCmd' => $qte]);
                $p->decrement('qteStock', $qte);
            }
        });

        return redirect()->route('commandes.index')->with('success', 'Commande mise à jour avec succès.');
    }

    public function ajouterProduit(Request $request, Commande $commande)
    {
        $donnees = $request->validate([
            'idProd' => ['required', 'exists:produit,idProd'],
            'qteCmd' => ['required', 'integer', 'min:1'],
        ], [
            'idProd.required' => 'Veuillez choisir un produit.',
            'idProd.exists'   => "Ce produit n'existe pas.",
            'qteCmd.required' => 'La quantité est obligatoire.',
            'qteCmd.integer'  => 'La quantité doit être un nombre entier.',
            'qteCmd.min'      => 'La quantité doit être au moins égale à 1.',
        ]);

        $erreur = DB::transaction(function () use ($donnees, $commande) {
            $produit = Produit::lockForUpdate()->find($donnees['idProd']);

            if ($commande->produits()->wherePivot('idProd', $produit->idProd)->exists()) {
                return 'Ce produit figure déjà dans la commande.';
            }

            if ($donnees['qteCmd'] > $produit->qteStock) {
                return "Stock insuffisant : il ne reste que {$produit->qteStock} unité(s) de « {$produit->designProd} ».";
            }

            $commande->produits()->attach($produit->idProd, ['qteCmd' => $donnees['qteCmd']]);
            $produit->decrement('qteStock', $donnees['qteCmd']);

            return null;
        });

        if ($erreur) {
            return back()->withErrors(['qteCmd' => $erreur])->withInput();
        }

        return redirect()
            ->route('commandes.show', $commande)
            ->with('success', 'Produit ajouté à la commande.');
    }

    public function editerProduit(Commande $commande, Produit $produit)
    {
        $ligne = $commande->produits()->wherePivot('idProd', $produit->idProd)->first();

        abort_if(! $ligne, 404);

        $qteActuelle = $ligne->pivot->qteCmd;
        $qteMax      = $produit->qteStock + $qteActuelle;

        return view('commandes.edit-produit', compact('commande', 'produit', 'qteActuelle', 'qteMax'));
    }

    public function modifierProduit(Request $request, Commande $commande, Produit $produit)
    {
        $donnees = $request->validate([
            'qteCmd' => ['required', 'integer', 'min:1'],
        ], [
            'qteCmd.required' => 'La quantité est obligatoire.',
            'qteCmd.integer'  => 'La quantité doit être un nombre entier.',
            'qteCmd.min'      => 'La quantité doit être au moins égale à 1.',
        ]);

        $erreur = DB::transaction(function () use ($donnees, $commande, $produit) {
            $ligne = $commande->produits()->wherePivot('idProd', $produit->idProd)->first();

            if (! $ligne) {
                return "Ce produit ne figure pas dans cette commande.";
            }

            $p = Produit::lockForUpdate()->find($produit->idProd);

            $ancienne   = $ligne->pivot->qteCmd;
            $nouvelle   = (int) $donnees['qteCmd'];
            $difference = $nouvelle - $ancienne;

            if ($difference > $p->qteStock) {
                $max = $p->qteStock + $ancienne;
                return "Stock insuffisant : vous pouvez commander au maximum {$max} unité(s) de « {$p->designProd} ».";
            }

            $commande->produits()->updateExistingPivot($p->idProd, ['qteCmd' => $nouvelle]);

            if ($difference > 0) {
                $p->decrement('qteStock', $difference);
            } elseif ($difference < 0) {
                $p->increment('qteStock', -$difference);
            }

            return null;
        });

        if ($erreur) {
            return back()->withErrors(['qteCmd' => $erreur])->withInput();
        }

        return redirect()
            ->route('commandes.show', $commande)
            ->with('success', 'Quantité mise à jour, stock ajusté.');
    }

    public function retirerProduit(Commande $commande, Produit $produit)
    {
        $erreur = DB::transaction(function () use ($commande, $produit) {
            $ligne = $commande->produits()->wherePivot('idProd', $produit->idProd)->first();

            if (! $ligne) {
                return "Ce produit ne figure pas dans cette commande.";
            }

            $qte = $ligne->pivot->qteCmd;

            $commande->produits()->detach($produit->idProd);

            Produit::lockForUpdate()->find($produit->idProd)->increment('qteStock', $qte);

            return null;
        });

        if ($erreur) {
            return back()->withErrors(['produit' => $erreur]);
        }

        return redirect()
            ->route('commandes.show', $commande)
            ->with('success', 'Produit retiré de la commande, stock remis à jour.');
    }

    // Supprime toute la commande et remet en stock les quantités de toutes ses lignes
    public function destroy(Commande $commande)
    {
        DB::transaction(function () use ($commande) {
            $commande->load('produits');

            foreach ($commande->produits as $produit) {
                Produit::lockForUpdate()->find($produit->idProd)
                       ->increment('qteStock', $produit->pivot->qteCmd);
            }

            $commande->produits()->detach();
            $commande->delete();
        });

        return redirect()
            ->route('commandes.index')
            ->with('success', 'Commande supprimée, stock remis à jour.');
    }
}