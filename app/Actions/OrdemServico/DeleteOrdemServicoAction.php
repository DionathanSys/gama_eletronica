<?php

namespace App\Actions\OrdemServico;

use App\Enums\StatusOrdemServicoEnum;
use App\Models\ItemNotaRemessa;
use App\Models\NotaEntrada;
use App\Models\OrdemServico;
use App\Traits\Notifica;

class DeleteOrdemServicoAction
{
    use Notifica;

    public static function exec(OrdemServico $ordemServico)
    {
        if ($ordemServico->status != StatusOrdemServicoEnum::PENDENTE->value) {
            self::notificaErro('Não é possível excluir uma ordem encerrada');
            return false;
        }

        if (! static::removeRelacionamentos($ordemServico)) {
            self::notificaErro('Não foi possível excluir os relacionamentos');
            return false;
        }

        $ordemServico->delete();

        self::notificaSucesso('Ordem excluída com sucesso');

    } 

    private static function removeRelacionamentos(OrdemServico $ordemServico):bool
    {
        $notaEntrada = $ordemServico->notaEntrada;
        $itensNota = ItemNotaRemessa::where('nota_entrada_id', $ordemServico->notaEntrada->id)->get();

        $ordemServico->update([
            'nota_entrada_id' => null,
        ]);
        
        $ordemServico->itemNotaRemessa->delete();

        if ($itensNota->count() == 1) {
            $notaEntrada->delete();
        }
        
        if ($ordemServico->itens_orcamento->isNotEmpty()) {
            $ordemServico->itens_orcamento->each(fn($item)=>$item->delete());
        }

        if ($ordemServico->itens->isNotEmpty()) {
            $ordemServico->itens->each(fn($item)=>$item->delete());
        }
        
        return true;
    }
}