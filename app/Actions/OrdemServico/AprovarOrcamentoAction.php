<?php

namespace App\Actions\OrdemServico;

use App\Models\Orcamento;
use App\Models\OrdemServico;
use Illuminate\Support\Arr;

class AprovarOrcamentoAction
{
    public static function exec(OrdemServico $ordemServico)
    {
        $itensOrcamento = $ordemServico->itens_orcamento;

        $arr = $itensOrcamento->reject(function($item){
            return $item['aprovado'] == 1;
        });

        dd($arr);

    }
}