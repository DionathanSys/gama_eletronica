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
        Schema::create('notas_entrada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parceiro_id')->constrained('parceiros');
            $table->string('natureza_operacao');
            $table->string('chave_nota')->nullable();
            $table->integer('nro_nota')->nullable();
            $table->integer('serie')->nullable();
            $table->date('data_fatura')->nullable();
            $table->date('data_entrada')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_entrada');
    }
};
