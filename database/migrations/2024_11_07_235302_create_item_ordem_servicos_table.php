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
        Schema::create('itens_ordem_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico');
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->decimal('quantidade', 8, 2);
            $table->decimal('valor_unitario', 8, 2);
            $table->decimal('valor_total', 8, 2);
            $table->decimal('desconto', 8, 2)->default(0);
            $table->string('observacao')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_ordem_servico');
    }
};
