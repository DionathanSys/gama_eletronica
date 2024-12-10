<?php

namespace App\Actions\NotaFiscalMercadoria;

use App\Actions\Fiscal\ValidaChaveAcessoNfAction;
use App\Enums\NaturezaOperacaoEnum;
use App\Models\ItemNotaRemessa;
use App\Models\NotaEntrada;
use App\Models\OrdemServico;
use App\Traits\Notifica;
use Illuminate\Database\Eloquent\Collection;

class VinculaNfRemessaAction
{
    use Notifica;

    public static function vinculaOrdem(OrdemServico $ordemServico, array $data)
    {
        $infoChaveAcesso = (new ValidaChaveAcessoNfAction($data['chave_nota']))->getInfo();

        if (!$infoChaveAcesso->status) {
            self::notificaErro('Chave de acesso inválida');
            return false;
        }
        
        if ($ordemServico->notaEntrada) {
            if ($data['chave_nota'] != $ordemServico->notaEntrada->chave_nota) {
                self::notificaErro('É necessário excluir o vínculo, para poder alterar a chave de acesso');
                return false;
            }
        }

        $data['serie'] = $infoChaveAcesso->serie;
        $data['nro_nota'] = $infoChaveAcesso->nroNota;

        $notaRemessa = static::registraNota($ordemServico, $data);

        $ordemServico->update([
            'nota_entrada_id' => $notaRemessa->id,
        ]);
    }

    private static function registraNota(OrdemServico $ordemServico, array $data): NotaEntrada
    {
        $notaEntrada = NotaEntrada::query()->firstOrCreate(
            [
                'chave_nota' => $data['chave_nota']
            ],
            [
                'parceiro_id' => $ordemServico->parceiro_id,
                'natureza_operacao' => NaturezaOperacaoEnum::REMESSA_CONSIGNACAO->value,
                'chave_nota' => $data['chave_nota'],
                'nro_nota' => $data['nro_nota'],
                'serie' => $data['serie'],
                'data_fatura' => $data['data_fatura'],
                'data_entrada' => now(),
            ]
        );

        $itemNotaRemessa = ItemNotaRemessa::query()->updateOrCreate(
            [
                'ordem_servico_id' => $ordemServico->id,
            ],
            [
                'parceiro_id' => $ordemServico->parceiro_id,
                'nota_entrada_id' => $notaEntrada->id,
                'ordem_servico_id' => $ordemServico->id,
                'codigo_item' => $data['codigo_item'],
                'ncm_item' => $data['ncm_item'],
                'valor' => $data['valor'],
            ]
        );

        $notaEntrada->update([
            'total' => $notaEntrada->itensRemessa->sum('valor'),
        ]);

        return $notaEntrada;
    }

}
