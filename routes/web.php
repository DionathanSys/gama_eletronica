<?php

use App\Models\ImpostoServico;
use App\Models\Parceiro;
use App\Models\Servico;
use App\Services\BuscaCNPJ;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    dd((new BuscaCNPJ(Parceiro::find(942)->nro_documento))->getInfo());
});

Route::prefix('ordem-servico')->group(function(){

    Route::get('/{id}/pdf', function($id){

    });
});
