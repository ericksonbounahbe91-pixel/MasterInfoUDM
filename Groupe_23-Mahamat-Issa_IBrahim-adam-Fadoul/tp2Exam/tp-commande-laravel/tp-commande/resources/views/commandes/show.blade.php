@extends('layouts.app')
@section('content')
<h3>Commande n°{{ $commande->idCmd }}</h3>
<p>
    Client : <strong>{{ $commande->client->prenomCli }} {{ $commande->client->nomCli }}</strong>
    ({{ $commande->client->region->nomRegion }}, {{ $commande->client->region->departement->nomDep }})
    — Date : {{ $commande->dateCmd->format('d/m/Y') }}
</p>
<table class="table bg-white align-middle">
    <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Qté</th><th>Sous-total</th><th></th></tr></thead>
    <tbody>
    @forelse($commande->produits as $p)
        <tr>
            <td>{{ $p->designProd }}</td>
            <td>{{ number_format($p->prixUProd, 0, ',', ' ') }} FCFA</td>
            <td>{{ $p->pivot->qteCmd }}</td>
            <td>{{ number_format($p->pivot->qteCmd * $p->prixUProd, 0, ',', ' ') }} FCFA</td>
            <td>
                <form method="POST" action="{{ route('commandes.retirer', [$commande, $p]) }}"
                      onsubmit="return confirm('Retirer ce produit de la commande ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Supprimer</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-muted">Aucun produit dans cette commande.</td></tr>
    @endforelse
    </tbody>
    <tfoot><tr><th colspan="3" class="text-end">Total</th><th colspan="2">{{ number_format($commande->total, 0, ',', ' ') }} FCFA</th></tr></tfoot>
</table>
<a href="{{ route('commandes.index') }}" class="btn btn-link">← Retour aux commandes</a>
@endsection
