@extends('layouts.app')

@section('titre', 'Modifier un produit')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Modifier le produit n° {{ $produit->idProd }}</h1>
        <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>

    <div class="card shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('produits.update', $produit) }}">
                @csrf
                {{-- Un formulaire HTML ne sait faire que GET et POST : @method simule PUT --}}
                @method('PUT')

                <div class="mb-3">
                    <label for="designProd" class="form-label">Désignation</label>
                    {{-- old(champ, valeur actuelle) : garde la saisie en cas d'erreur, sinon la valeur de la base --}}
                    <input type="text" name="designProd" id="designProd" class="form-control"
                           value="{{ old('designProd', $produit->designProd) }}" required>
                </div>

                <div class="mb-3">
                    <label for="qteStock" class="form-label">Quantité en stock</label>
                    <input type="number" name="qteStock" id="qteStock" class="form-control" min="0"
                           value="{{ old('qteStock', $produit->qteStock) }}" required>
                </div>

                <div class="mb-3">
                    <label for="prixUProd" class="form-label">Prix unitaire (FCFA)</label>
                    <input type="number" name="prixUProd" id="prixUProd" class="form-control" min="0" step="0.01"
                           value="{{ old('prixUProd', $produit->prixUProd) }}" required>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>
@endsection