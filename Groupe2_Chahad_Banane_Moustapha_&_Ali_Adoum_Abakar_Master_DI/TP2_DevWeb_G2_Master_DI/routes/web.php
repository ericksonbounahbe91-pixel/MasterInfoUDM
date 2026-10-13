<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProduitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentification (accessible uniquement aux visiteurs non connectés)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Maximum 5 tentatives par minute (protection contre le brute force)
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Application : réservée aux utilisateurs connectés
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Produits (create AVANT les routes à paramètre)
    Route::get('/produits', [ProduitController::class, 'index'])->name('produits.index');
    Route::get('/produits/create', [ProduitController::class, 'create'])->name('produits.create');
    Route::post('/produits', [ProduitController::class, 'store'])->name('produits.store');
    Route::get('/produits/{produit}/edit', [ProduitController::class, 'edit'])->name('produits.edit');
    Route::put('/produits/{produit}', [ProduitController::class, 'update'])->name('produits.update');

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');

    // Commandes (create AVANT /commandes/{commande})
    Route::get('/commandes', [CommandeController::class, 'index'])->name('commandes.index');
    Route::get('/commandes/create', [CommandeController::class, 'create'])->name('commandes.create');
    Route::post('/commandes', [CommandeController::class, 'store'])->name('commandes.store');
    Route::get('/commandes/{commande}/edit', [CommandeController::class, 'edit'])->name('commandes.edit');
    Route::put('/commandes/{commande}', [CommandeController::class, 'update'])->name('commandes.update');
    Route::get('/commandes/{commande}', [CommandeController::class, 'show'])->name('commandes.show');
    Route::delete('/commandes/{commande}', [CommandeController::class, 'destroy'])->name('commandes.destroy');

    // Lignes de commande
    Route::post('/commandes/{commande}/produits', [CommandeController::class, 'ajouterProduit'])
        ->name('commandes.produits.store');
    Route::get('/commandes/{commande}/produits/{produit}/edit', [CommandeController::class, 'editerProduit'])
        ->name('commandes.produits.edit');
    Route::put('/commandes/{commande}/produits/{produit}', [CommandeController::class, 'modifierProduit'])
        ->name('commandes.produits.update');
    Route::delete('/commandes/{commande}/produits/{produit}', [CommandeController::class, 'retirerProduit'])
        ->name('commandes.produits.destroy');
});
