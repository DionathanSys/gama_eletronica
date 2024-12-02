<?php

namespace App\Actions\OrdemServico;

use App\Models\ItemOrdemServico;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\Log;

class UpdateValorOrdemActions
{

    public static function exec(OrdemServico $ordemServico)
    {   
        $itensOrdemServico = ItemOrdemServico::where('ordem_servico_id', $ordemServico->id)->get();

        $valorItens = $itensOrdemServico->sum('valor_total');
        $valorDesconto = $ordemServico->desconto;

        $valorTotal = $valorItens - $valorDesconto;

        // dump($itensOrdemServico->sum('valor_total'));

        $ordemServico->update([
            'valor_total' => $valorTotal
            ]
        );

        $ordemServico->refresh();
        dd($ordemServico);

    }
}