<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Gestion des commandes')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Icônes Bootstrap (facultatif : sans internet, le menu fonctionne sans les icônes) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* Style des liens du menu latéral */
        .sidebar .nav-link {
            color: #cfd4da;
            font-size: 1.15rem;
            padding: .85rem 1rem;
        }
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }
        .sidebar .nav-link.active {
            background: #0d6efd;
            color: #fff;
        }

        /* Sur grand écran : menu fixe à gauche, sur toute la hauteur */
        @media (min-width: 992px) {
            .sidebar {
                width: 270px;
                height: 100vh;
                position: sticky;
                top: 0;
                align-self: flex-start;
                overflow-y: auto;
            }
        }
    </style>
</head>
<body class="bg-light">

    {{-- d-lg-flex : sidebar et contenu côte à côte sur grand écran, empilés sur petit écran --}}
    <div class="d-lg-flex">

        {{-- Menu latéral --}}
        <aside class="sidebar bg-dark text-white flex-shrink-0">

            <div class="d-flex justify-content-between align-items-center p-3 mb-lg-3">
                <a class="text-white text-decoration-none fs-4 fw-bold" href="{{ route('dashboard') }}">
                    Gestion des commandes
                </a>

                {{-- Bouton qui ouvre/ferme le menu sur petit écran uniquement --}}
                <button class="btn btn-outline-light d-lg-none" type="button"
                        data-bs-toggle="collapse" data-bs-target="#menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            {{-- collapse + d-lg-block : replié sur mobile, toujours visible sur grand écran --}}
            <div class="collapse d-lg-block px-3 pb-3" id="menu">
                <ul class="nav nav-pills flex-column gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}"
                           href="{{ route('clients.index') }}">
                            <i class="bi bi-people me-2"></i>Clients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('produits.*') ? 'active' : '' }}"
                           href="{{ route('produits.index') }}">
                            <i class="bi bi-box-seam me-2"></i>Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('commandes.*') ? 'active' : '' }}"
                           href="{{ route('commandes.index') }}">
                            <i class="bi bi-cart3 me-2"></i>Commandes
                        </a>
                    </li>
                </ul>

                <hr class="border-secondary my-3">
                <div class="text-white-50 mb-2 px-1">
                    <i class="bi bi-person-circle me-2"></i>{{ auth()->user()->name }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light w-100">
                        <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                    </button>
                </form>
            </div>
        </aside>

        {{-- Contenu de la page (min-width: 0 évite que les tableaux dépassent de l'écran) --}}
        <div class="flex-grow-1" style="min-width: 0;">
            <main class="p-4">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>