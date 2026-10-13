@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Liste des commandes</h3>
    <a href="{{ route('commandes.create') }}" class="btn btn-primary">+ Nouvelle commande</a>
</div>
<div class="table-responsive">
<table class="table table-striped bg-white align-middle">
    <thead><tr><th>N°</th><th>Date</th><th>Client</th><th>Produits</th><th>Total</th><th></th></tr></thead>
    <tbody>
    @foreach($commandes as $c)
        <tr>
            <td>{{ $c->idCmd }}</td>
            <td>{{ $c->dateCmd->format('d/m/Y') }}</td>
            <td>{{ $c->client->prenomCli }} {{ $c->client->nomCli }}</td>
            <td>{{ $c->produits->count() }}</td>
            <td>{{ number_format($c->total, 0, ',', ' ') }} FCFA</td>
            <td><a href="{{ route('commandes.show', $c) }}" class="btn btn-sm btn-outline-secondary">Détail</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
{{ $commandes->links('pagination::bootstrap-5') }}
@endsection
