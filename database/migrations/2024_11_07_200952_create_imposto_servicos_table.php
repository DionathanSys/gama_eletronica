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
        Schema::create('imposto_servicos', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo_municipio');
            $table->string('municipio');
            $table->string('codigo_servico');
            $table->decimal('aliq_iss');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['codigo_municipio', 'codigo_servico']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imposto_servicos');
    }
};
