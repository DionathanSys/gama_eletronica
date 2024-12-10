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
            $table->dropColumn('data_fatura');
            $table->dropColumn('total');
            
            $table->date('data_emissao')->after('serie')->nullable();
            $table->date('data_entrada_saida')->after('data_emissao')->nullable();
            $table->json('notas_referenciadas')->after('data_entrada_saida')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_saida', function (Blueprint $table) {
            $table->dropColumn('notas_referenciadas');
            $table->dropColumn('data_entrada_saida');
            $table->dropColumn('data_emissao');
        
            $table->date('data_fatura')->after('serie')->nullable();
            $table->decimal('total', 8, 2)->after('data_fatura')->default(0);
            
        });
    }
};
