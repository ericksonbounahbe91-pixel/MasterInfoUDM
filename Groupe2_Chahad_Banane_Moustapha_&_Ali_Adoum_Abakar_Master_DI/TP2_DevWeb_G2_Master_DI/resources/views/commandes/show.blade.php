@extends('layouts.app')

@section('titre', 'Commande n° ' . $commande->idCmd)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h3 mb-0">Commande n° {{ $commande->idCmd }}</h1>

        <div class="d-flex gap-2">
            <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Retour aux commandes
            </a>

            {{-- Suppression de toute la commande, avec confirmation --}}
            <form method="POST" action="{{ route('commandes.destroy', $commande) }}"
                  onsubmit="return confirm('Supprimer définitivement la commande n° {{ $commande->idCmd }} ? Les quantités seront remises en stock.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Supprimer la commande</button>
            </form>
        </div>
    </div>

    <p class="text-muted">
        Passée le {{ $commande->dateCmd->format('d/m/Y') }} par
        <strong>{{ $commande->client->prenomCli }} {{ $commande->client->nomCli }}</strong>
    </p>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Produits de la commande</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produit</th>
                                <th class="text-end">Prix unitaire</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-end">Sous-total</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($commande->produits as $produit)
                                <tr>
                                    <td>{{ $produit->designProd }}</td>
                                    <td class="text-end">{{ number_format($produit->prixUProd, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-center">{{ $produit->pivot->qteCmd }}</td>
                                    <td class="text-end">
                                        {{ number_format($produit->pivot->qteCmd * $produit->prixUProd, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('commandes.produits.edit', [$commande, $produit]) }}"
                                           class="btn btn-sm btn-warning">Modifier</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Aucun produit dans cette commande pour l'instant.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end">
                                    {{ number_format(
                                        $commande->produits->sum(fn ($p) => $p->pivot->qteCmd * $p->prixUProd),
                                        0, ',', ' ') }} FCFA
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Ajouter un produit</div>
                <div class="card-body">

                    @if ($produitsDisponibles->isEmpty())
                        <div class="alert alert-warning mb-0">
                            Aucun produit disponible (tous sont déjà dans la commande ou en rupture de stock).
                        </div>
                    @else
                        <form method="POST" action="{{ route('commandes.produits.store', $commande) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="idProd" class="form-label">Produit</label>
                                <select name="idProd" id="idProd" class="form-select" required>
                                    <option value="">-- Choisir un produit --</option>
                                    @foreach ($produitsDisponibles as $produit)
                                        <option value="{{ $produit->idProd }}"
                                            @selected(old('idProd') == $produit->idProd)>
                                            {{ $produit->designProd }} (stock : {{ $produit->qteStock }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="qteCmd" class="form-label">Quantité</label>
                                <input type="number" name="qteCmd" id="qteCmd" class="form-control"
                                       min="1" value="{{ old('qteCmd', 1) }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Ajouter à la commande
                            </button>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection