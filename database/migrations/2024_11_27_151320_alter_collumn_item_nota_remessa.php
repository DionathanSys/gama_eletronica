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
        Schema::table('itens_nota_remessa', function (Blueprint $table) {
            $table->dropColumn('chave_nota');
            $table->foreignId('nota_entrada_id')->nullable()->after('parceiro_id')->constrained('notas_entrada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens_nota_remessa', function (Blueprint $table) {
            $table->dropForeign(['nota_entrada_id']);
            $table->dropColumn('nota_entrada_id');
            $table->string('chave_nota')->nullable();
        });
    }
};
