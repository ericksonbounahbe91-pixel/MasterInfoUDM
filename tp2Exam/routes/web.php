<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProduitController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('produits', ProduitController::class)->except(['show', 'destroy']);

Route::resource('commandes', CommandeController::class)->only(['index', 'create', 'store', 'show']);
Route::delete('commandes/{commande}/produits/{produit}', [CommandeController::class, 'retirerProduit'])
    ->name('commandes.retirer');

Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
