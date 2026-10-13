<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TP Gestion des commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Gestion commandes</a>
        <div class="navbar-nav flex-row gap-3">
            <a class="nav-link" href="{{ route('dashboard') }}">Tableau de bord</a>
            <a class="nav-link" href="{{ route('produits.index') }}">Produits</a>
            <a class="nav-link" href="{{ route('commandes.index') }}">Commandes</a>
            <a class="nav-link" href="{{ route('clients.index') }}">Clients</a>
        </div>
    </div>
</nav>
<div class="container pb-5">
    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul></div>
    @endif
    @yield('content')
</div>
@stack('scripts')
</body>
</html>
