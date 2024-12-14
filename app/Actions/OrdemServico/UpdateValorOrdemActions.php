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
        
        Log::debug('UpdateValorOrdemActions', [
            'valorItens' => $valorItens,
            'valorDesconto - NEW' => $valorDesconto,
            'valorTotal - NEW' => $valorTotal,
            'valorDesconto - OLD' => $ordemServico->desconto,
            'valorTotal - OLD' => $ordemServico->valor_total,
            'change' => $ordemServico->getChanges(),
        ]);


        $ordemServico->update([
            'valor_total' => $valorTotal
            ]
        );
        
        $ordemServico->refresh();
        

    }
}