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
        Schema::table('nota_saida_ordem_servico', function (Blueprint $table) {
            $table->string('natureza_operacao')->after('nota_saida_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_saida_ordem_servico', function (Blueprint $table) {
            $table->dropColumn('natureza_operacao');
        });
    }
};
