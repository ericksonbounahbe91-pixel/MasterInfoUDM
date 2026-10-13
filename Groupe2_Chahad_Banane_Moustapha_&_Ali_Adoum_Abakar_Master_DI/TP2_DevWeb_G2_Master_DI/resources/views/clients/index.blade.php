@extends('layouts.app')

@section('titre', 'Clients')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Liste des clients</h1>
        <a href="{{ route('clients.create') }}" class="btn btn-primary">+ Ajouter un client</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Région</th>
                        <th>Département</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td>{{ $client->idCli }}</td>
                            <td>{{ $client->nomCli }}</td>
                            <td>{{ $client->prenomCli }}</td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->region->nomRegion }}</td>
                            <td>{{ $client->region->departement->nomDep }}</td>
                            <td class="text-center">
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-primary">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Aucun client.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $clients->links() }}</div>
@endsection