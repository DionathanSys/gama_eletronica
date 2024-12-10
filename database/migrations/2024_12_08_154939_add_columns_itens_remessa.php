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
        Schema::table('itens_nota_remessa', function (Blueprint $table){
            $table->string('st_icms')->after('ncm_item')->nullable();
            $table->string('st_pis')->after('ncm_item')->nullable();
            $table->string('st_cofins')->after('ncm_item')->nullable();
            $table->string('cfop')->after('ncm_item')->nullable();
            $table->string('unidade_comercial')->after('codigo_item')->nullable();
            $table->string('descricao')->after('codigo_item')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens_nota_remessa', function (Blueprint $table){
            $table->dropColumn('st_icms');
            $table->dropColumn('st_pis');
            $table->dropColumn('st_cofins');
            $table->dropColumn('cfop');
            $table->dropColumn('unidade_comercial');
            $table->dropColumn('descricao');
        });
    }
};
