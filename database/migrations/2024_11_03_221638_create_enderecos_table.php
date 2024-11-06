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
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parceiro_id')->constrained('parceiros');
            $table->string('rua', 75);
            $table->string('numero', 7);
            $table->string('complemento', 10)->nullable();
            $table->string('bairro', 75);
            $table->string('codigo_municipio', 10)->nullable();
            $table->string('cidade', 50);
            $table->string('estado',2)->default('SC');
            $table->string('cep');
            $table->string('pais')->default('Brasil');
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
        Schema::dropIfExists('enderecos');
    }
};
