<?php

namespace App\Actions\NotaFiscalMercadoria;

use App\Models\ItemNotaRemessa;
use App\Models\NotaEntrada;
use App\Models\OrdemServico;
use Illuminate\Database\Eloquent\Collection;

class VinculaNfRemessaAction
{
    public static function vinculaOrdem(OrdemServico $ordemServico, array $data)
    {
        $itemNotaRemessa = static::registraNota($ordemServico, $data);
     
        // $ordemServico->update([
        //     'nota_entrada_id' => $itemNotaRemessa->id,
        // ]);

    } 

    private static function registraNota(OrdemServico $ordemServico, array $data): ItemNotaRemessa
    {
        // return ItemNotaRemessa::query()->firstOrCreate(
            //         [
            //             'chave_nota' => $chaveNf
            //         ],
            //         [
            //             'parceiro_id' => $parceiro_id,
            //             'natureza_operacao' => 'REMESSA DE MERCADORIA OU BEM PARA CONSERTO OU REPARO',
            //             'chave_nota' => $chaveNf,
            //         ]
            //         );
    
        return ItemNotaRemessa::create([
            'parceiro_id' => $ordemServico->parceiro_id,
            'ordem_servico_id' => $ordemServico->id,
            'chave_nota' => $data['chave_nota'],
            'codigo_item' => $data['codigo_item'],
            'ncm_item' => $data['ncm_item'],
            'valor' => $data['valor'],
        ]);
    }
}