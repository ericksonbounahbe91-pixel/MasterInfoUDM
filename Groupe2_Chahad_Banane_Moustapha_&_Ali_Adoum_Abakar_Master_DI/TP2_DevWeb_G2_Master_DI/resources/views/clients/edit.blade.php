@extends('layouts.app')

@section('titre', 'Modifier un client')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Modifier un client</h1>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>

    <div class="card shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('clients.update', $client) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nomCli" class="form-label">Nom</label>
                    <input type="text" name="nomCli" id="nomCli" class="form-control"
                           value="{{ old('nomCli', $client->nomCli) }}" required>
                </div>

                <div class="mb-3">
                    <label for="prenomCli" class="form-label">Prénom</label>
                    <input type="text" name="prenomCli" id="prenomCli" class="form-control"
                           value="{{ old('prenomCli', $client->prenomCli) }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="{{ old('email', $client->email) }}" required>
                </div>

                <div class="mb-3">
                    <label for="nomDep" class="form-label">Département <span class="text-danger">*</span></label>
                    <input type="text" name="nomDep" id="nomDep" class="form-control" list="liste-departements"
                           value="{{ old('nomDep', $client->nomDep) }}" required>
                    <datalist id="liste-departements">
                        @foreach ($departements as $nom)
                            <option value="{{ $nom }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="mb-3">
                    <label for="nomRegion" class="form-label">Région <span class="text-danger">*</span></label>
                    <input type="text" name="nomRegion" id="nomRegion" class="form-control" list="liste-regions"
                           value="{{ old('nomRegion', $client->nomRegion) }}" required>
                    <datalist id="liste-regions">
                        @foreach ($regions as $nom)
                            <option value="{{ $nom }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection