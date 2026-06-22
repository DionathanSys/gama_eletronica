<?php

use App\Http\Controllers\Api\Migracao\ParceirosMigrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('migracao')->group(function () {
    Route::get('parceiros', [ParceirosMigrationController::class, 'index']);
});
