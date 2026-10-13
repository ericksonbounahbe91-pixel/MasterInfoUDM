<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departement', function (Blueprint $table) {
            $table->id('idDep');              // Clé primaire auto-incrémentée
            $table->string('nomDep', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departement');
    }
};