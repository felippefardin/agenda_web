<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Alteramos a coluna para string para suportar "high", "normal", "urgent"
            $table->string('priority')->default('normal')->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Caso precise voltar atrás (ajuste conforme o tipo original)
            $table->string('priority')->change(); 
        });
    }
};