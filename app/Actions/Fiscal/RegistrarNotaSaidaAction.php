<?php

namespace App\Actions\Fiscal;

use App\Enums\NaturezaOperacaoEnum;
use App\Models\NotaSaida;
use App\Traits\Notifica;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RegistrarNotaSaidaAction
{

    public static function handle(Collection $ordensServico): NotaSaida
    {
        $ordensServico->load(['notaRetorno', 'itemNotaRemessa']);

        self::validarOrdensServico($ordensServico);

        return DB::transaction(function () use ($ordensServico) {

            $notaSaida = NotaSaida::create([
                'parceiro_id'        => $ordensServico->first()->parceiro_id,
                'natureza_operacao'  => NaturezaOperacaoEnum::RETORNO_MERCADORIA->value,
                'notas_referenciadas' => self::notasReferenciadas($ordensServico),
            ]);

            $ordensServico->each(function ($ordem) use ($notaSaida) {
                return $ordem->notaRetorno()->attach($notaSaida->id, ['natureza_op' => NaturezaOperacaoEnum::RETORNO_MERCADORIA->value]);
            });

            return $notaSaida;
        });
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

        if ($ordensServico->contains(fn($ordem) => is_null($ordem->notaRemessa))) {
            throw new Exception('Uma ou mais ordens de serviço não possuem vínculo com NFe de retorno.');
        }
    }
}
