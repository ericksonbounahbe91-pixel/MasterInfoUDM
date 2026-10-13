@extends('layouts.app')

@section('titre', 'Nouvelle commande')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Nouvelle commande</h1>
        <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>

    @if ($clients->isEmpty())
        <div class="alert alert-warning">
            Aucun client n'existe encore.
            <a href="{{ route('clients.create') }}" class="alert-link">Ajoute d'abord un client</a>.
        </div>
    @else
        <div class="card shadow-sm" style="max-width: 600px;">
            <div class="card-body">
                <form method="POST" action="{{ route('commandes.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="idCli" class="form-label">Client</label>
                        <select name="idCli" id="idCli" class="form-select" required>
                            <option value="">-- Choisir un client --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->idCli }}" @selected(old('idCli') == $client->idCli)>
                                    {{ $client->nomCli }} {{ $client->prenomCli }} — {{ $client->region->nomRegion }} ({{ $client->region->departement->nomDep }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="dateCmd" class="form-label">Date de la commande</label>
                        <input type="date" name="dateCmd" id="dateCmd" class="form-control"
                               value="{{ old('dateCmd', now()->format('Y-m-d')) }}" required>
                    </div>

                    <button type="submit" class="btn btn-success">Créer la commande</button>
                    <div class="form-text mt-2">Tu pourras ensuite ajouter les produits de cette commande.</div>
                </form>
            </div>
        </div>
    @endif
@endsection