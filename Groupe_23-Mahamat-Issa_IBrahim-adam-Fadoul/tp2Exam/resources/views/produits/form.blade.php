@extends('layouts.app')
@section('content')
<h3>{{ $produit->exists ? 'Modifier le produit #'.$produit->idProd : 'Nouveau produit' }}</h3>
<form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded border"
      action="{{ $produit->exists ? route('produits.update', $produit) : route('produits.store') }}">
    @csrf
    @if($produit->exists) @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label">Désignation</label>
        <input name="designProd" class="form-control" value="{{ old('designProd', $produit->designProd) }}" required>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Quantité en stock</label>
            <input type="number" min="0" name="qteStock" class="form-control" value="{{ old('qteStock', $produit->qteStock) }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Prix unitaire (FCFA)</label>
            <input type="number" min="0" step="0.01" name="prixUProd" class="form-control" value="{{ old('prixUProd', $produit->prixUProd) }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Photo</label>
        @if($produit->photoProd)
            <div class="mb-2"><img src="{{ asset('storage/'.$produit->photoProd) }}" height="80" alt=""></div>
        @endif
        <input type="file" name="photoProd" accept="image/*" class="form-control">
    </div>
    <button class="btn btn-primary">Enregistrer</button>
    <a href="{{ route('produits.index') }}" class="btn btn-link">Annuler</a>
</form>
@endsection
