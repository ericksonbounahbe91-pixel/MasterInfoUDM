<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index()
    {
        $produits = Produit::orderBy('designProd')->paginate(10);

        return view('produits.index', compact('produits'));
    }

    // Formulaire d'ajout
    public function create()
    {
        return view('produits.create');
    }

    // Enregistre un nouveau produit
    public function store(Request $request)
    {
        Produit::create($this->valider($request));

        return redirect()->route('produits.index')->with('success', 'Produit ajouté avec succès.');
    }

    public function edit(Produit $produit)
    {
        return view('produits.edit', compact('produit'));
    }

    public function update(Request $request, Produit $produit)
    {
        $produit->update($this->valider($request));

        return redirect()->route('produits.index')->with('success', 'Produit modifié avec succès.');
    }

    // Règles de validation communes à l'ajout et à la modification
    private function valider(Request $request): array
    {
        return $request->validate([
            'designProd' => ['required', 'string', 'max:150'],
            'qteStock'   => ['required', 'integer', 'min:0'],
            'prixUProd'  => ['required', 'numeric', 'min:0'],
        ], [
            'designProd.required' => 'La désignation est obligatoire.',
            'designProd.max'      => 'La désignation ne doit pas dépasser 150 caractères.',
            'qteStock.required'   => 'Le stock est obligatoire.',
            'qteStock.integer'    => 'Le stock doit être un nombre entier.',
            'qteStock.min'        => 'Le stock ne peut pas être négatif.',
            'prixUProd.required'  => 'Le prix est obligatoire.',
            'prixUProd.numeric'   => 'Le prix doit être un nombre.',
            'prixUProd.min'       => 'Le prix ne peut pas être négatif.',
        ]);
    }
}