<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departements', function (Blueprint $t) {
            $t->id('idDep');
            $t->string('nomDep');
            $t->timestamps();
        });

        Schema::create('regions', function (Blueprint $t) {
            $t->id('idRegion');
            $t->string('nomRegion');
            $t->unsignedBigInteger('idDep');
            $t->foreign('idDep')->references('idDep')->on('departements')->cascadeOnDelete();
            $t->timestamps();
        });

        Schema::create('clients', function (Blueprint $t) {
            $t->id('idCli');
            $t->string('nomCli');
            $t->string('prenomCli');
            $t->string('email')->unique();
            $t->string('photo')->nullable();
            $t->unsignedBigInteger('idRegion')->index();
            $t->foreign('idRegion')->references('idRegion')->on('regions');
            $t->timestamps();
        });

        Schema::create('produits', function (Blueprint $t) {
            $t->id('idProd');
            $t->string('designProd');
            $t->unsignedInteger('qteStock')->default(0);
            $t->decimal('prixUProd', 12, 2);
            $t->string('photoProd')->nullable();
            $t->timestamps();
        });

        Schema::create('commandes', function (Blueprint $t) {
            $t->id('idCmd');
            $t->date('dateCmd')->index();
            $t->unsignedBigInteger('idCli')->index();
            $t->foreign('idCli')->references('idCli')->on('clients')->cascadeOnDelete();
            $t->timestamps();
        });

        // Table d'association : clé primaire composée (idCmd, idProd)
        Schema::create('ligne_commandes', function (Blueprint $t) {
            $t->unsignedBigInteger('idCmd');
            $t->unsignedBigInteger('idProd');
            $t->unsignedInteger('qteCmd');
            $t->primary(['idCmd', 'idProd']);
            $t->index('idProd');
            $t->foreign('idCmd')->references('idCmd')->on('commandes')->cascadeOnDelete();
            $t->foreign('idProd')->references('idProd')->on('produits');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_commandes');
        Schema::dropIfExists('commandes');
        Schema::dropIfExists('produits');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('departements');
    }
};
