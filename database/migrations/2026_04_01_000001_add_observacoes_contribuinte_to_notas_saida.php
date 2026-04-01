<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notas_saida', function (Blueprint $table) {
            $table->json('observacoes_contribuinte')->nullable()->after('notas_referenciadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_saida', function (Blueprint $table) {
            $table->dropColumn('observacoes_contribuinte');
        });
    }
};
