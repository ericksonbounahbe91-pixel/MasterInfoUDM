<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit', function (Blueprint $table) {
            $table->id('idProd');
            $table->string('designProd', 150);
            $table->unsignedInteger('qteStock')->default(0);   // jamais négatif
            $table->decimal('prixUProd', 10, 2);               // prix avec 2 décimales
            $table->string('photoProd')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit');
    }
};