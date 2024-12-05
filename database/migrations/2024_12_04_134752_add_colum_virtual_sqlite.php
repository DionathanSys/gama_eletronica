<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddVirtualColumnToEquipamentosTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verifica se o banco de dados configurado é SQLite
        if (env('DB_CONNECTION') === 'sqlite') {
            // Cria a tabela temporária para a alteração
            DB::statement("
                CREATE TABLE equipamentos_temp AS SELECT * FROM equipamentos;
            ");

            Schema::drop('equipamentos'); // Remove a tabela original

            Schema::create('equipamentos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parceiro_id')->constrained('parceiros');
                $table->string('descricao')->nullable();
                $table->string('nro_serie')->nullable();
                $table->string('descricao_nro_serie')->virtualAs("descricao || ' ' || nro_serie"); // Coluna virtual
                $table->string('modelo')->nullable();
                $table->string('marca')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('updated_by')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });

            // Restaura os dados da tabela temporária
            DB::statement("
                INSERT INTO equipamentos (id, descricao, nro_serie, modelo, marca, created_by, updated_by, created_at, updated_at, deleted_at)
                SELECT id, descricao, nro_serie, modelo, marca, created_by, updated_by, created_at, updated_at, deleted_at FROM equipamentos_temp;
            ");

            // Remove a tabela temporária
            DB::statement("DROP TABLE equipamentos_temp;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('DB_CONNECTION') === 'sqlite') {
            // Remove a tabela atual com a coluna gerada
            DB::statement("
                CREATE TABLE equipamentos_temp AS SELECT id, descricao, nro_serie, modelo, marca, created_by, updated_by, created_at, updated_at, deleted_at FROM equipamentos;
            ");

            Schema::drop('equipamentos'); // Remove a tabela com a coluna gerada

            Schema::create('equipamentos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parceiro_id')->constrained('parceiros');
                $table->string('descricao')->nullable();
                $table->string('nro_serie')->nullable();
                $table->string('modelo')->nullable();
                $table->string('marca')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('updated_by')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });

            // Restaura os dados da tabela temporária
            DB::statement("
                INSERT INTO equipamentos (id, descricao, nro_serie, modelo, marca, created_by, updated_by, created_at, updated_at, deleted_at)
                SELECT id, descricao, nro_serie, modelo, marca, created_by, updated_by, created_at, updated_at, deleted_at FROM equipamentos_temp;
            ");

            // Remove a tabela temporária
            DB::statement("DROP TABLE equipamentos_temp;");
        }
    }
}

