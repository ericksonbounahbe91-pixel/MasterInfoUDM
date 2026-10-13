@extends('layouts.app')

@section('titre', 'Ajouter un client')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Ajouter un client</h1>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>

    <div class="card shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('clients.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="nomCli" class="form-label">Nom</label>
                    <input type="text" name="nomCli" id="nomCli" class="form-control"
                           value="{{ old('nomCli') }}" required>
                </div>

                <div class="mb-3">
                    <label for="prenomCli" class="form-label">Prénom</label>
                    <input type="text" name="prenomCli" id="prenomCli" class="form-control"
                           value="{{ old('prenomCli') }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label for="nomDep" class="form-label">Département <span class="text-danger">*</span></label>
                    {{-- list="..." propose les départements déjà saisis, mais on peut en taper un nouveau --}}
                    <input type="text" name="nomDep" id="nomDep" class="form-control" list="liste-departements"
                           value="{{ old('nomDep') }}" required>
                    <datalist id="liste-departements">
                        @foreach ($departements as $nom)
                            <option value="{{ $nom }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="mb-3">
                    <label for="nomRegion" class="form-label">Région <span class="text-danger">*</span></label>
                    <input type="text" name="nomRegion" id="nomRegion" class="form-control" list="liste-regions"
                           value="{{ old('nomRegion') }}" required>
                    <datalist id="liste-regions">
                        @foreach ($regions as $nom)
                            <option value="{{ $nom }}">
                        @endforeach
                    </datalist>
                </div>

                <button type="submit" class="btn btn-success">Enregistrer</button>
            </form>
        </div>
    </div>
@endsection