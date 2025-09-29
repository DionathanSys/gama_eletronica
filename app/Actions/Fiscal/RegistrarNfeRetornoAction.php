<?php

namespace App\Actions\Fiscal;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\StatusNotaFiscalEnum;
use App\Models\ItemNotaSaida;
use App\Models\NotaSaida;
use App\Traits\Notifica;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RegistrarNfeRetornoAction
{

    public static function handle(Collection $ordensServico): NotaSaida
    {
        $ordensServico->load(['notaRetorno', 'itemNotaRemessa']);

        self::validarOrdensServico($ordensServico);

        return DB::transaction(function () use ($ordensServico) {

            $notaSaida = NotaSaida::create([
                'parceiro_id'           => $ordensServico->first()->parceiro_id,
                'natureza_operacao'     => NaturezaOperacaoEnum::RETORNO_MERCADORIA->value,
                'notas_referenciadas'   => self::notasReferenciadas($ordensServico),
                'status'                => StatusNotaFiscalEnum::PENDENTE,
            ]);

            $itens = self::registrarItensNotaSaida($notaSaida, $ordensServico);

            $notaSaida->itens()->createMany($itens);

            $ordensServico->each(function ($ordem) use ($notaSaida) {
                return $ordem->notaRetorno()->attach($notaSaida->id, ['natureza_op' => NaturezaOperacaoEnum::RETORNO_MERCADORIA->value]);
            });

            return $notaSaida;
        });
    }

    private static function registrarItensNotaSaida(NotaSaida $notaSaida, Collection $ordensServico): array
    {
        $itens = $ordensServico->map(function ($ordem) use ($notaSaida) {
            return [
                'pendente'                  => true,
                'nota_saida_id'             => $notaSaida->id,
                'codigo_produto'            => $ordem->itemNotaRemessa->codigo_item,
                'descricao_produto'         => $ordem->equipamento->descricao,
                'quantidade'                => 1,
                'valor_unitario'            => $ordem->itemNotaRemessa->valor,
                'valor_total'               => $ordem->itemNotaRemessa->valor,
                'unidade'                   => 'UN',
                'ncm'                       => $ordem->itemNotaRemessa->ncm_item,
                'cfop'                      => $notaSaida->parceiro->enderecos->first()->estado == 'SC' ? config('nfe.cfop.intraestadual.nfe_retorno') : config('nfe.cfop.interestadual.nfe_retorno'),
                'impostos'                   => [
                    'icms'      => (object) ['situacao_tributaria' => config('nfe.icms.situacao_tributaria.nfe_retorno')],
                    'pis'       => (object) ['situacao_tributaria' => config('nfe.pis.situacao_tributaria')],
                    'cofins'    => (object) ['situacao_tributaria' => config('nfe.cofins.situacao_tributaria')],
                ],
            ];
        });

        return $itens->toArray();
    }

    private static function notasReferenciadas(Collection $ordensServico): array
    {
        return $ordensServico
                    ->filter()                                                                                          // Remover valores nulos
                    ->map(fn($ordem)        => [
                        'chave_nota'        => $ordem->notaEntrada->chave_nota,
                        'data_fatura'       => Carbon::parse($ordem->notaEntrada->data_fatura)->format('d/m/Y'),
                        'nro_nota'          => $ordem->notaEntrada->nro_nota,
                    ])
                    ->unique('chave_nota')                                                                              // Remove duplicados com base na chave da nota
                    ->mapWithKeys(fn($nota) => [
                        $nota['chave_nota'] => "Nro. {$nota['nro_nota']} - {$nota['data_fatura']}",
                    ])
                    ->toArray();
    }

    private static function validarOrdensServico(Collection $ordensServico): void
    {
        if ($ordensServico->contains(fn($ordem) => !$ordem->notaRetorno->isEmpty())) {
            throw new Exception('Existem ordens de serviço já vinculadas a uma ou mais nota(s) de retorno.');
        }

        if ($ordensServico->unique('parceiro_id')->count() > 1) {
            throw new Exception('Todas as ordens de serviço devem pertencer ao mesmo cliente.');
        }

        //TODO: Rever mensagem de erro
        if ($ordensServico->contains(fn($ordem) => is_null($ordem->itemNotaRemessa))) {
            throw new Exception('Uma ou mais ordens de serviço não possuem vínculo com NFe de retorno.');
        }
    }
}
