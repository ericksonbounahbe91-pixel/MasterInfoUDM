<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client', function (Blueprint $table) {
            $table->id('idCli');
            $table->string('nomCli', 100);
            $table->string('prenomCli', 100);
            $table->string('email', 150)->unique();   // deux clients ne peuvent pas avoir le même email
            $table->string('photo')->nullable();      // chemin de la photo, facultatif
            $table->unsignedBigInteger('idRegion');

            $table->foreign('idRegion')
                  ->references('idRegion')->on('region')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client');
    }
};