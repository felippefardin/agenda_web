<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No SQLite, precisamos remover e recriar para limpar a CHECK constraint antiga
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('priority');
        });

        Schema::table('events', function (Blueprint $table) {
            // Agora criamos como string pura, que aceitará 'high', 'normal', 'urgent', etc.
            $table->string('priority')->default('normal')->after('time');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('priority');
        });

        Schema::table('events', function (Blueprint $table) {
            // Reverte para o estado original se necessário
            $table->enum('priority', ['normal', 'urgent'])->default('normal')->after('time');
        });
    }
};