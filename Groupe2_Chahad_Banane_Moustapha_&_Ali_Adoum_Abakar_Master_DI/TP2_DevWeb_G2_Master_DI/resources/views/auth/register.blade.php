@extends('layouts.auth')
@section('titre', 'Création de compte')
@section('content')
    <h2 class="h5 text-center mb-4">Créer un compte</h2>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nom complet</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   required autofocus autocomplete="name" maxlength="100">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autocomplete="email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe <small class="text-muted">(8 caractères minimum)</small></label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required minlength="8" autocomplete="new-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="form-control" required minlength="8" autocomplete="new-password">
        </div>

        <button class="btn btn-primary w-100 py-2">Créer mon compte</button>
    </form>

    <p class="text-center text-muted mt-4 mb-0">
        Vous avez déjà un compte ?
        <a href="{{ route('login') }}">Se connecter</a>
    </p>
@endsection
