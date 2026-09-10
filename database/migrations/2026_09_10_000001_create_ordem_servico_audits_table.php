<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordem_servico_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')
                ->nullable()
                ->constrained('ordens_servico')
                ->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('event', 50);
            $table->string('source', 100)->nullable();
            $table->string('request_id', 64)->nullable();
            $table->date('old_data_ordem')->nullable();
            $table->date('new_data_ordem')->nullable();
            $table->json('context')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->timestamps();

            $table->index(['ordem_servico_id', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_audits');
    }
};
