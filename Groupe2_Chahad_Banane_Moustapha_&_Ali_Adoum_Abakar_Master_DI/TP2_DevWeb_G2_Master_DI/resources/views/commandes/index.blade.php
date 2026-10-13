@extends('layouts.app')

@section('titre', 'Commandes')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Liste des commandes</h1>
        <a href="{{ route('commandes.create') }}" class="btn btn-primary">+ Nouvelle commande</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>N°</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Département</th>
                        <th>Région</th>
                        <th class="text-center">Produits</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($commandes as $commande)
                        <tr>
                            <td>{{ $commande->idCmd }}</td>
                            <td>{{ $commande->dateCmd->format('d/m/Y') }}</td>
                            <td>{{ $commande->client->prenomCli }} {{ $commande->client->nomCli }}</td>
                            <td>{{ $commande->client->region->departement->nomDep }}</td>
                            <td>{{ $commande->client->region->nomRegion }}</td>
                            <td class="text-center">{{ $commande->produits->count() }}</td>
                            <td class="text-end">
                                {{ number_format(
                                    $commande->produits->sum(fn ($p) => $p->pivot->qteCmd * $p->prixUProd),
                                    0, ',', ' ') }} FCFA
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('commandes.show', $commande) }}"
                                   class="btn btn-sm btn-primary">Voir</a>
                                <a href="{{ route('commandes.edit', $commande) }}"
                                   class="btn btn-sm btn-secondary">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Aucune commande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $commandes->links() }}</div>
@endsection