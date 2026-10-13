<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligneCommande', function (Blueprint $table) {
            $table->unsignedBigInteger('idCmd');
            $table->unsignedBigInteger('idProd');
            $table->unsignedInteger('qteCmd');

            // Clé primaire composée : un produit ne peut apparaître qu'une fois dans une commande
            $table->primary(['idCmd', 'idProd']);

            // Si on supprime une commande, ses lignes disparaissent avec elle
            $table->foreign('idCmd')
                  ->references('idCmd')->on('commande')
                  ->onDelete('cascade');

            // On ne peut pas supprimer un produit déjà commandé
            $table->foreign('idProd')
                  ->references('idProd')->on('produit')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligneCommande');
    }
};