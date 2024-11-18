<?php

namespace App\Actions\OrdemServico;

use App\Models\ItemOrdemServico;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AprovarOrcamentoAction
{
    public static function exec(OrdemServico $ordemServico)
    {
        $itensOrcamento = $ordemServico->itens_orcamento;

        $itensOrcamento->each(function ($item) {
            
            unset($item['id']);
            $item['created_by'] = Auth::id();
            $item['updated_by'] = Auth::id();
            $item['created_at'] = now();
            $item['updated_at'] = now();

            if (! $item['aprovado']) {
                
                unset($item['aprovado']);

                ItemOrdemServico::create($item->toArray());

                $item->update([
                    'aprovado' => 1,
                ]);

            }
        });
    }
}
