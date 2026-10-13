@extends('layouts.auth')
@section('titre', 'Connexion')
@section('content')
    <h2 class="h5 text-center mb-4">Connectez-vous pour accéder à la plateforme</h2>

    <form method="POST" action="{{ route('login.attempt') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autofocus autocomplete="email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>

        <button class="btn btn-primary w-100 py-2">Se connecter</button>
    </form>

    <p class="text-center text-muted mt-4 mb-0">
        Pas encore de compte ?
        <a href="{{ route('register') }}">Créer un compte</a>
    </p>
@endsection
