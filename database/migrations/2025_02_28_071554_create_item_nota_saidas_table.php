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
        Schema::create('itens_nota_saida', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_saida_id')->constrained('notas_saida')->cascadeOnDelete();
            $table->string('codigo_produto', 20);
            $table->string('descricao_produto', 255);
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->boolean('pendente')->default(true);
            $table->string('ncm', 8);
            $table->string('cfop', 4);
            $table->string('unidade', 2);
            $table->json('impostos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_nota_saida');
    }
};
