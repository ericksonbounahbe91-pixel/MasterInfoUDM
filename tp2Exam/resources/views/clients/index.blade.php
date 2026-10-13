@extends('layouts.app')
@section('content')
<h3 class="mb-3">Liste des clients</h3>
<div class="table-responsive">
<table class="table table-striped bg-white align-middle">
    <thead><tr><th>Photo</th><th>#</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Région</th><th>Département</th></tr></thead>
    <tbody>
    @foreach($clients as $c)
        <tr>
            <td>@if($c->photo)<img src="{{ asset('storage/'.$c->photo) }}" height="36" class="rounded-circle" alt="">@endif</td>
            <td>{{ $c->idCli }}</td>
            <td>{{ $c->nomCli }}</td>
            <td>{{ $c->prenomCli }}</td>
            <td>{{ $c->email }}</td>
            <td>{{ $c->region->nomRegion }}</td>
            <td>{{ $c->region->departement->nomDep }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
{{ $clients->links('pagination::bootstrap-5') }}
@endsection
