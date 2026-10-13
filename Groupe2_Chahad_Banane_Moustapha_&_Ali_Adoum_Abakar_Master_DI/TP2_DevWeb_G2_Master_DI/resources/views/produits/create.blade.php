@extends('layouts.app')

@section('titre', 'Ajouter un produit')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Ajouter un produit</h1>
        <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>

    <div class="card shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('produits.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="designProd" class="form-label">Désignation</label>
                    <input type="text" name="designProd" id="designProd" class="form-control"
                           value="{{ old('designProd') }}" required>
                </div>

                <div class="mb-3">
                    <label for="qteStock" class="form-label">Quantité en stock</label>
                    <input type="number" name="qteStock" id="qteStock" class="form-control" min="0"
                           value="{{ old('qteStock', 0) }}" required>
                </div>

                <div class="mb-3">
                    <label for="prixUProd" class="form-label">Prix unitaire (FCFA)</label>
                    <input type="number" name="prixUProd" id="prixUProd" class="form-control" min="0" step="0.01"
                           value="{{ old('prixUProd') }}" required>
                </div>

                <button type="submit" class="btn btn-success">Enregistrer</button>
            </form>
        </div>
    </div>
@endsection