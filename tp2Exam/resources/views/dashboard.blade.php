@extends('layouts.app')
@section('content')
<h3 class="mb-3">Tableau de bord — lignes de commande</h3>

<div class="row g-3 mb-4">
    @foreach([
        ["Chiffre d'affaires", number_format($caTotal, 0, ',', ' ').' FCFA'],
        ['Commandes', $nbCommandes],
        ['Panier moyen', number_format($panierMoyen, 0, ',', ' ').' FCFA'],
        ['Unités vendues', number_format($qteVendue, 0, ',', ' ')],
    ] as [$titre, $valeur])
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">{{ $titre }}</div>
                <div class="fs-4 fw-bold">{{ $valeur }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6"><div class="card"><div class="card-body">
        <h6>Top 5 produits (CA en FCFA)</h6><canvas id="cTop"></canvas>
    </div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-body">
        <h6>CA par région (FCFA)</h6><canvas id="cRegion"></canvas>
    </div></div></div>
    <div class="col-12"><div class="card"><div class="card-body">
        <h6>Évolution mensuelle du CA (FCFA)</h6><canvas id="cMois" height="90"></canvas>
    </div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h6 class="text-danger">Alertes de stock faible (&lt; 10)</h6>
            @forelse($stockFaible as $p)
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ $p->designProd }}</span><strong>{{ $p->qteStock }}</strong>
                </div>
            @empty
                <span class="text-muted">Aucun produit en alerte.</span>
            @endforelse
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card"><div class="card-body">
            <h6>Analyse de performance (chargement de la liste des commandes)</h6>
            <table class="table table-sm mb-2">
                <thead><tr><th>Méthode</th><th>Requêtes SQL</th><th>Temps (ms)</th></tr></thead>
                <tbody>
                @foreach($perf as $m)
                    <tr><td>{{ $m['nom'] }}</td><td>{{ $m['requetes'] }}</td><td>{{ $m['ms'] }}</td></tr>
                @endforeach
                </tbody>
            </table>
            <small class="text-muted">Le lazy loading déclenche 1 + N (+ N) requêtes ; <code>with()</code> les ramène à 3 ; un agrégat SQL à 1.</small>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('cTop'), {
        type: 'bar',
        data: { labels: @json($topProduits->pluck('label')), datasets: [{ label: 'CA', data: @json($topProduits->pluck('ca')) }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });
    new Chart(document.getElementById('cRegion'), {
        type: 'doughnut',
        data: { labels: @json($parRegion->pluck('label')), datasets: [{ data: @json($parRegion->pluck('ca')) }] }
    });
    new Chart(document.getElementById('cMois'), {
        type: 'line',
        data: { labels: @json($parMois->pluck('label')), datasets: [{ label: 'CA', data: @json($parMois->pluck('ca')), tension: .3, fill: true }] },
        options: { plugins: { legend: { display: false } } }
    });
</script>
@endpush
