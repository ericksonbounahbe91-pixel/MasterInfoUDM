@extends('layouts.app')
@section('content')
<h3>Nouvelle commande</h3>
<form method="POST" action="{{ route('commandes.store') }}" class="bg-white p-4 rounded border">
    @csrf
    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">Client</label>
            <select name="idCli" class="form-select" required>
                <option value="">— choisir —</option>
                @foreach($clients as $c)
                    <option value="{{ $c->idCli }}" @selected(old('idCli') == $c->idCli)>{{ $c->nomCli }} {{ $c->prenomCli }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="dateCmd" class="form-control" value="{{ old('dateCmd', now()->toDateString()) }}" required>
        </div>
    </div>

    <h5>Produits de la commande</h5>
    <table class="table" id="lignes">
        <thead><tr><th>Produit</th><th style="width:140px">Quantité</th><th style="width:60px"></th></tr></thead>
        <tbody></tbody>
    </table>
    <button type="button" id="ajouter" class="btn btn-outline-primary mb-3">+ Ajouter un produit</button>
    <div><button class="btn btn-success">Enregistrer la commande</button></div>
</form>

<template id="tpl">
    <tr>
        <td>
            <select name="lignes[__i__][idProd]" class="form-select" required>
                <option value="">— choisir —</option>
                @foreach($produits as $p)
                    <option value="{{ $p->idProd }}">{{ $p->designProd }} (stock : {{ $p->qteStock }} — {{ number_format($p->prixUProd, 0, ',', ' ') }} FCFA)</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" min="1" name="lignes[__i__][qteCmd]" class="form-control" value="1" required></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger retirer">×</button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
    let i = 0;
    const corps = document.querySelector('#lignes tbody');
    function ajouter() {
        corps.insertAdjacentHTML('beforeend', document.querySelector('#tpl').innerHTML.replaceAll('__i__', i++));
    }
    document.querySelector('#ajouter').addEventListener('click', ajouter);
    corps.addEventListener('click', e => { if (e.target.classList.contains('retirer')) e.target.closest('tr').remove(); });
    ajouter();
</script>
@endpush
