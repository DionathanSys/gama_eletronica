<?php

use App\Models\ImpostoServico;
use App\Models\Servico;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return view('welcome');
});

Route::prefix('ordem-servico')->group(function(){

    Route::get('/{id}/pdf', function($id){

    });
});
