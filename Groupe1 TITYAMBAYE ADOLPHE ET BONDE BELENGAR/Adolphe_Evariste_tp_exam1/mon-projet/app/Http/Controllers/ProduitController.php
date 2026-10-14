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

    public function create()
    {
        return view('produits.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designProd' => 'required|string|max:150',
            'qteStock'   => 'required|integer|min:0',
            'prixUProd'  => 'required|numeric|min:0',
            'photoProd'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photoProd')) {
            $data['photoProd'] = $request->file('photoProd')->store('produits', 'public');
        }

        Produit::create($data);

        return redirect()->route('produits.index')->with('success', 'Produit ajouté avec succès.');
    }

    public function edit(Produit $produit)
    {
        return view('produits.edit', compact('produit'));
    }

    public function update(Request $request, Produit $produit)
    {
        $data = $request->validate([
            'designProd' => 'required|string|max:150',
            'qteStock'   => 'required|integer|min:0',
            'prixUProd'  => 'required|numeric|min:0',
            'photoProd'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photoProd')) {
            $data['photoProd'] = $request->file('photoProd')->store('produits', 'public');
        } else {
            unset($data['photoProd']); // on garde l'ancienne photo
        }

        $produit->update($data);

        return redirect()->route('produits.index')->with('success', 'Produit modifié avec succès.');
    }
}
