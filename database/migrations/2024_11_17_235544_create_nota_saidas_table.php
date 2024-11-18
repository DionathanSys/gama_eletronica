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
        Schema::create('notas_saida', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parceiro_id')->constrained('parceiros');
            $table->foreignId('fatura_id')->nullable()->constrained('faturas');
            $table->string('natureza_operacao');
            $table->string('chave_nota')->nullable();
            $table->integer('nro_nota')->nullable();
            $table->integer('serie')->nullable();
            $table->date('data_fatura');
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_saida');
    }
};
