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
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->string('status_processo')
                ->after('status')
                ->nullable();
            $table->date('data_encerrado')
                ->after('data_ordem')
                ->nullable();
            $table->string('tipo_manutencao')
                ->after('prioridade')
                ->nullable();
        });

        Schema::table('itens_ordem_servico', function (Blueprint $table) {
            $table->boolean('garantia')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropColumn('status_processo');
            $table->dropColumn('data_encerrado');
            $table->dropColumn('tipo_manutencao');
        });

        Schema::table('itens_ordem_servico', function (Blueprint $table) {
            $table->dropColumn('garantia');
        });
    }
};
