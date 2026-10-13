<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commande', function (Blueprint $table) {
            $table->index('dateCmd');
        });
    }

    public function down(): void
    {
        Schema::table('commande', function (Blueprint $table) {
            $table->dropIndex(['dateCmd']);
        });
    }
};