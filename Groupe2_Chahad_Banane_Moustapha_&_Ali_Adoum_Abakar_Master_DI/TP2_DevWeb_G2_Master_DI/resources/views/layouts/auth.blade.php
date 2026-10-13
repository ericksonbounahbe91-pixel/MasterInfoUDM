<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Connexion') - Gestion des commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #212529 0%, #0d6efd 100%); min-height: 100vh; }
        .auth-card { max-width: 440px; width: 100%; border: 0; border-radius: 16px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="card auth-card shadow-lg">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-cart3 text-primary" style="font-size: 2.5rem;"></i>
                <h1 class="h4 fw-bold mt-2 mb-0">Gestion des commandes</h1>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
