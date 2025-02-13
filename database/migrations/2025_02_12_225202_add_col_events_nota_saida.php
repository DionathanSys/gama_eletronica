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
            $table->json('eventos')->after('notas_referenciadas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_saida', function (Blueprint $table) {
            $table->dropColumn('eventos');
        });
    }
};
