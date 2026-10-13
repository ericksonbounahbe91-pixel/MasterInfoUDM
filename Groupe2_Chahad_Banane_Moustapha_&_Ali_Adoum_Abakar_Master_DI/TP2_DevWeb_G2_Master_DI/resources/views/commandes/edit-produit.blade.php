@extends('layouts.app')

@section('titre', 'Modifier un produit de la commande')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Modifier la commande n° {{ $commande->idCmd }}</h1>
        <a href="{{ route('commandes.show', $commande) }}" class="btn btn-outline-secondary btn-sm">
            ← Retour à la commande
        </a>
    </div>

    {{-- Modification de la quantité --}}
    <div class="card shadow-sm" style="max-width: 600px;">
        <div class="card-body">

            <dl class="row mb-4">
                <dt class="col-sm-6">Produit</dt>
                <dd class="col-sm-6">{{ $produit->designProd }}</dd>

                <dt class="col-sm-6">Prix unitaire</dt>
                <dd class="col-sm-6">{{ number_format($produit->prixUProd, 0, ',', ' ') }} FCFA</dd>

                <dt class="col-sm-6">Quantité actuelle</dt>
                <dd class="col-sm-6">{{ $qteActuelle }}</dd>

                <dt class="col-sm-6">Quantité maximale possible</dt>
                <dd class="col-sm-6">{{ $qteMax }}</dd>
            </dl>

            <form method="POST" action="{{ route('commandes.produits.update', [$commande, $produit]) }}">
                @csrf
                {{-- @method('PUT') : le navigateur ne sait envoyer que GET et POST, Laravel simule PUT --}}
                @method('PUT')

                <div class="mb-3">
                    <label for="qteCmd" class="form-label">Nouvelle quantité</label>
                    <input type="number" name="qteCmd" id="qteCmd" class="form-control"
                           min="1" max="{{ $qteMax }}"
                           value="{{ old('qteCmd', $qteActuelle) }}" required>
                </div>

                <button type="submit" class="btn btn-success">Enregistrer</button>
                <a href="{{ route('commandes.show', $commande) }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>

    {{-- Retrait du produit de la commande --}}
    <div class="card border-danger mt-4" style="max-width: 600px;">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <strong>Retirer ce produit de la commande</strong>
                <div class="small text-muted">Les {{ $qteActuelle }} unité(s) seront remises en stock.</div>
            </div>

            <form method="POST" action="{{ route('commandes.produits.destroy', [$commande, $produit]) }}"
                  onsubmit="return confirm('Retirer « {{ addslashes($produit->designProd) }} » de la commande ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Retirer</button>
            </form>
        </div>
    </div>
@endsection