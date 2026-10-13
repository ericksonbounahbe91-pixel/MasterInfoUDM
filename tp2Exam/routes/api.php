<?php

use App\Http\Controllers\Api\ClientApiController;
use App\Http\Controllers\Api\CommandeApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProduitApiController;
use Illuminate\Support\Facades\Route;

// Toutes ces routes sont préfixées par /api (ex. http://IP:8000/api/produits)
Route::get('dashboard', [DashboardApiController::class, 'index']);

Route::get('produits', [ProduitApiController::class, 'index']);
Route::post('produits', [ProduitApiController::class, 'store']);
Route::put('produits/{produit}', [ProduitApiController::class, 'update']);

Route::get('clients', [ClientApiController::class, 'index']);

Route::get('commandes', [CommandeApiController::class, 'index']);
Route::get('commandes/{commande}', [CommandeApiController::class, 'show']);
Route::post('commandes', [CommandeApiController::class, 'store']);
Route::delete('commandes/{commande}/produits/{produit}', [CommandeApiController::class, 'retirerProduit']);
