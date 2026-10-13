<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region', function (Blueprint $table) {
            $table->id('idRegion');
            $table->string('nomRegion', 100);
            $table->unsignedBigInteger('idDep');   // même type que departement.idDep

            // Clé étrangère : une région appartient à un département
            $table->foreign('idDep')
                  ->references('idDep')->on('departement')
                  ->onDelete('restrict');          // interdit de supprimer un département qui a des régions
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region');
    }
};