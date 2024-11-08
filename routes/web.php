<?php

use App\Models\ImpostoServico;
use App\Models\Servico;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $imp = (new ImpostoServico())->create([
        'codigo_municipio' => 123456,
        'aliq_iss' => 3.00,
    ]);

    $serv = (new Servico())->create([
        'nome' => 'Teste',
        'descricao' => 'Teste Desc',
        'imposto_servico_id' => 1,
    ]);

    

    dd($serv->impostos);

});
