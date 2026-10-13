@extends('layouts.app')

@section('titre', 'Tableau de bord')

@section('content')
    <h1 class="h3 mb-3">Tableau de bord</h1>

    {{-- Cartes d'indicateurs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body">
                    <div class="text-muted small">Commandes</div>
                    <div class="fs-3 fw-bold">{{ $nbCommandes }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body">
                    <div class="text-muted small">Chiffre d'affaires</div>
                    <div class="fs-3 fw-bold">{{ number_format($caTotal, 0, ',', ' ') }} <small>FCFA</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-info h-100">
                <div class="card-body">
                    <div class="text-muted small">Panier moyen</div>
                    <div class="fs-3 fw-bold">{{ number_format($panierMoyen, 0, ',', ' ') }} <small>FCFA</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-danger h-100">
                <div class="card-body">
                    <div class="text-muted small">Produits à stock faible</div>
                    <div class="fs-3 fw-bold">{{ $stockFaible->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold">Chiffre d'affaires par mois (FCFA)</div>
                <div class="card-body"><canvas id="chartMois" height="120"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold">CA par région</div>
                <div class="card-body"><canvas id="chartDepartements"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold">Top 5 produits vendus (quantités)</div>
                <div class="card-body"><canvas id="chartProduits"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold">Top 5 clients (CA)</div>
                <table class="table mb-0 align-middle">
                    <tbody>
                        @foreach ($topClients as $i => $c)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>{{ $c->client }}</td>
                                <td class="text-end">{{ number_format($c->ca, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Stock faible --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">Produits à réapprovisionner (stock &lt; 20)</div>
        <table class="table mb-0 align-middle">
            <tbody>
                @forelse ($stockFaible->take(5) as $p)
                    <tr>
                        <td>{{ $p->designProd }}</td>
                        <td class="text-end"><span class="badge bg-danger">{{ $p->qteStock }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('produits.edit', $p) }}" class="btn btn-sm btn-warning">Modifier</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="text-center text-muted">Aucun produit en stock faible.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mesure de performance --}}
    <p class="text-muted small text-end">
        Page calculée avec {{ $nbRequetes }} requêtes SQL en {{ $dureeMs }} ms.
    </p>
@endsection

@push('scripts')
    {{-- Chart.js via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        // Les données calculées en PHP sont transmises au JavaScript au format JSON
        const charts = @json($charts);

        new Chart(document.getElementById('chartMois'), {
            type: 'line',
            data: {
                labels: charts.mois.labels,
                datasets: [{ label: 'CA', data: charts.mois.data, borderColor: '#0d6efd', tension: 0.3, fill: false }]
            },
            options: { plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('chartDepartements'), {
            type: 'doughnut',
            data: {
                labels: charts.departements.labels,
                datasets: [{ data: charts.departements.data }]
            }
        });

        new Chart(document.getElementById('chartProduits'), {
            type: 'bar',
            data: {
                labels: charts.produits.labels,
                datasets: [{ label: 'Quantité vendue', data: charts.produits.data, backgroundColor: '#198754' }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } } }
        });
    </script>
@endpush