<?php

namespace App\Actions\NotaFiscalMercadoria;

use App\Models\NotaEntrada;
use App\Models\OrdemServico;
use Illuminate\Database\Eloquent\Collection;

class VinculaNfRemessaAction
{
    public static function vinculaOrdem(OrdemServico $ordemServico, string $chaveNf)
    {
        $notaEntrada = static::registraNota($ordemServico->parceiro_id, $chaveNf);
     
        $ordemServico->update([
            'nota_entrada_id' => $notaEntrada->id,
        ]);
    } 

    private static function registraNota(int $parceiro_id, string $chaveNf): NotaEntrada
    {
        return NotaEntrada::query()->firstOrCreate(
                [
                    'chave_nota' => $chaveNf
                ],
                [
                    'parceiro_id' => $parceiro_id,
                    'natureza_operacao' => 'REMESSA DE MERCADORIA OU BEM PARA CONSERTO OU REPARO',
                    'chave_nota' => $chaveNf,
                ]
                );
    }
}