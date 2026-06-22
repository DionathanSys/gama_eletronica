<?php

use App\Http\Controllers\Api\Migracao\ContatosMigrationController;
use App\Http\Controllers\Api\Migracao\EnderecosMigrationController;
use App\Http\Controllers\Api\Migracao\EquipamentosMigrationController;
use App\Http\Controllers\Api\Migracao\OrdensServicoMigrationController;
use App\Http\Controllers\Api\Migracao\ParceirosMigrationController;
use App\Http\Controllers\Api\Migracao\ServicosMigrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('migracao')->group(function () {
    Route::get('contatos', [ContatosMigrationController::class, 'index']);
    Route::get('enderecos', [EnderecosMigrationController::class, 'index']);
    Route::get('equipamentos', [EquipamentosMigrationController::class, 'index']);
    Route::get('ordens-servico', [OrdensServicoMigrationController::class, 'index']);
    Route::get('parceiros', [ParceirosMigrationController::class, 'index']);
    Route::get('servicos', [ServicosMigrationController::class, 'index']);
});
