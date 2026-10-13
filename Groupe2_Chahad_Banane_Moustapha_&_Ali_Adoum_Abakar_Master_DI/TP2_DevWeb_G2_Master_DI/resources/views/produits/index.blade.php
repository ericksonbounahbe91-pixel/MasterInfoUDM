@extends('layouts.app')

@section('titre', 'Produits')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Liste des produits</h1>
        <a href="{{ route('produits.create') }}" class="btn btn-primary">+ Ajouter un produit</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Désignation</th>
                        <th class="text-center">Prix unitaire</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produits as $produit)
                        <tr>
                            <td>{{ $produit->idProd }}</td>
                            <td>{{ $produit->designProd }}</td>
                            <td class="text-center">{{ number_format($produit->prixUProd, 0, ',', ' ') }} FCFA</td>
                            <td class="text-center">{{ $produit->qteStock }} </td>
                            <td class="text-center">
                                <a href="{{ route('produits.edit', $produit) }}" class="btn btn-sm btn-primary">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucun produit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $produits->links() }}</div>
@endsection