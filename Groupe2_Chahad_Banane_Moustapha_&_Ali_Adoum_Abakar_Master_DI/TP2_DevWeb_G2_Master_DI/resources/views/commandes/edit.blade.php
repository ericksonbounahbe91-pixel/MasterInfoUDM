@extends('layouts.app')

@section('titre', 'Modifier la commande n° ' . $commande->idCmd)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Modifier la commande n° {{ $commande->idCmd }}</h1>
        <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>

    <form method="POST" action="{{ route('commandes.update', $commande) }}">
        @csrf
        @method('PUT')

        {{-- Informations de la commande --}}
        <div class="card shadow-sm mb-4" style="max-width: 800px;">
            <div class="card-header fw-bold">Informations de la commande</div>
            <div class="card-body">

                <div class="mb-3">
                    <label for="idCli" class="form-label">Client</label>
                    <select name="idCli" id="idCli" class="form-select" required>
                        @foreach ($clients as $client)
                            <option value="{{ $client->idCli }}"
                                @selected(old('idCli', $commande->idCli) == $client->idCli)>
                                {{ $client->nomCli }} {{ $client->prenomCli }}
                                — {{ $client->region->nomRegion }} ({{ $client->region->departement->nomDep }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-0">
                    <label for="dateCmd" class="form-label">Date de la commande</label>
                    <input type="date" name="dateCmd" id="dateCmd" class="form-control"
                           value="{{ old('dateCmd', $commande->dateCmd->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        {{-- Produits déjà dans la commande --}}
        <div class="card shadow-sm mb-4" style="max-width: 800px;">
            <div class="card-header fw-bold">Produits de la commande</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produit</th>
                            <th class="text-end">Prix unitaire</th>
                            <th style="width: 140px;">Quantité</th>
                            <th class="text-center">Retirer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($commande->produits as $produit)
                            <tr>
                                <td>{{ $produit->designProd }}</td>
                                <td class="text-end">{{ number_format($produit->prixUProd, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    {{-- max = stock disponible + quantité déjà réservée par cette ligne --}}
                                    <input type="number" name="lignes[{{ $produit->idProd }}]" class="form-control"
                                           min="1" max="{{ $produit->qteStock + $produit->pivot->qteCmd }}"
                                           value="{{ old('lignes.' . $produit->idProd, $produit->pivot->qteCmd) }}" required>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" name="retirer[]" value="{{ $produit->idProd }}"
                                           class="form-check-input"
                                           @checked(in_array($produit->idProd, old('retirer', [])))>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Aucun produit dans cette commande pour l'instant.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($commande->produits->isNotEmpty())
                <div class="card-footer small text-muted">
                    Coche « Retirer » pour supprimer un produit de la commande. Les quantités retirées retournent en stock.
                </div>
            @endif
        </div>

        {{-- Ajout d'un produit --}}
        <div class="card shadow-sm mb-4" style="max-width: 800px;">
            <div class="card-header fw-bold">Ajouter un produit (facultatif)</div>
            <div class="card-body">
                @if ($produitsDisponibles->isEmpty())
                    <div class="text-muted">
                        Aucun produit disponible (tous sont déjà dans la commande ou en rupture de stock).
                    </div>
                @else
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nouveauProd" class="form-label">Produit</label>
                            <select name="nouveau[idProd]" id="nouveauProd" class="form-select">
                                <option value="">-- Ne pas ajouter de produit --</option>
                                @foreach ($produitsDisponibles as $produit)
                                    <option value="{{ $produit->idProd }}"
                                        @selected(old('nouveau.idProd') == $produit->idProd)>
                                        {{ $produit->designProd }} (stock : {{ $produit->qteStock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="nouveauQte" class="form-label">Quantité</label>
                            <input type="number" name="nouveau[qteCmd]" id="nouveauQte" class="form-control"
                                   min="1" value="{{ old('nouveau.qteCmd', 1) }}">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Mettre à jour</button>
            <a href="{{ route('commandes.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>

    {{-- Suppression de la commande : formulaire séparé (on ne peut pas imbriquer deux formulaires) --}}
    <div class="card border-danger mt-4" style="max-width: 800px;">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <strong>Supprimer cette commande</strong>
                <div class="small text-muted">Toutes ses quantités seront remises en stock.</div>
            </div>

            <form method="POST" action="{{ route('commandes.destroy', $commande) }}"
                  onsubmit="return confirm('Supprimer définitivement la commande n° {{ $commande->idCmd }} ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Supprimer la commande</button>
            </form>
        </div>
    </div>
@endsection