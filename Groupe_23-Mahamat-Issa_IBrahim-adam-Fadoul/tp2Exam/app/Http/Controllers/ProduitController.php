<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    // Consigne 2 : liste des produits
    public function index()
    {
        $produits = Produit::orderBy('designProd')->paginate(10);
        return view('produits.index', compact('produits'));
    }

    public function create()
    {
        return view('produits.form', ['produit' => new Produit]);
    }

    public function store(Request $request)
    {
        $data = $this->valider($request);
        if ($request->hasFile('photoProd')) {
            $data['photoProd'] = $request->file('photoProd')->store('produits', 'public');
        }
        Produit::create($data);

        return redirect()->route('produits.index')->with('ok', 'Produit enregistré.');
    }

    // Consigne 3 : modifier un produit
    public function edit(Produit $produit)
    {
        return view('produits.form', compact('produit'));
    }

    public function update(Request $request, Produit $produit)
    {
        $data = $this->valider($request);
        if ($request->hasFile('photoProd')) {
            if ($produit->photoProd) {
                Storage::disk('public')->delete($produit->photoProd);
            }
            $data['photoProd'] = $request->file('photoProd')->store('produits', 'public');
        }
        $produit->update($data);

        return redirect()->route('produits.index')->with('ok', 'Produit modifié.');
    }

    private function valider(Request $request): array
    {
        $data = $request->validate([
            'designProd' => 'required|string|max:150',
            'qteStock'   => 'required|integer|min:0',
            'prixUProd'  => 'required|numeric|min:0',
            'photoProd'  => 'nullable|image|max:2048',
        ]);
        unset($data['photoProd']); // la photo est traitée à part
        return $data;
    }
}
