@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Liste des produits</h3>
    <a href="{{ route('produits.create') }}" class="btn btn-primary">+ Nouveau produit</a>
</div>
<div class="table-responsive">
<table class="table table-striped bg-white align-middle">
    <thead><tr><th>Photo</th><th>#</th><th>Désignation</th><th>Stock</th><th>Prix unitaire</th><th></th></tr></thead>
    <tbody>
    @foreach($produits as $p)
        <tr>
            <td>@if($p->photoProd)<img src="{{ asset('storage/'.$p->photoProd) }}" height="40" alt="">@endif</td>
            <td>{{ $p->idProd }}</td>
            <td>{{ $p->designProd }}</td>
            <td><span class="{{ $p->qteStock < 10 ? 'text-danger fw-bold' : '' }}">{{ $p->qteStock }}</span></td>
            <td>{{ number_format($p->prixUProd, 0, ',', ' ') }} FCFA</td>
            <td><a href="{{ route('produits.edit', $p) }}" class="btn btn-sm btn-outline-secondary">Modifier</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
{{ $produits->links('pagination::bootstrap-5') }}
@endsection
