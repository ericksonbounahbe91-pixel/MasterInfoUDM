<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitApiController extends Controller
{
    public function index()
    {
        return Produit::orderBy('designProd')->get()->map(fn ($p) => $this->fmt($p))->values();
    }

    public function store(Request $request)
    {
        $produit = Produit::create($this->valider($request));
        return response()->json($this->fmt($produit), 201);
    }

    public function update(Request $request, Produit $produit)
    {
        $produit->update($this->valider($request));
        return response()->json($this->fmt($produit));
    }

    private function valider(Request $request): array
    {
        return $request->validate([
            'designProd' => 'required|string|max:150',
            'qteStock'   => 'required|integer|min:0',
            'prixUProd'  => 'required|numeric|min:0',
        ]);
    }

    private function fmt(Produit $p): array
    {
        return [
            'idProd'     => (int) $p->idProd,
            'designProd' => $p->designProd,
            'qteStock'   => (int) $p->qteStock,
            'prixUProd'  => (float) $p->prixUProd,
        ];
    }
}
