<?php

namespace App\Actions\NotaFiscalMercadoria;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\StatusNotaFiscalEnum;
use App\Models\NotaSaida;
use Carbon\Carbon;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RegistrarNfeRetornoAction
{
    public static function handle(Collection $ordensServico): NotaSaida
    {
        $notasRemessa = self::getNfeRemessa($ordensServico);
      
        if(empty($notasRemessa)){
            Notification::make()
                ->color('danger')
                ->title('Falha durante solicitação')
                ->body('Não foram encontradas NFe\'s de remessa!');

            return false;
        }

        if (!self::validate($ordensServico)){
            Notification::make()
                ->color('danger')
                ->title('Falha durante solicitação')
                ->body('Erro de validação dos dados!');

            return false;
        }

        return DB::transaction(function () use ($ordensServico, $notasRemessa) {

            $notaSaida = NotaSaida::create([
                'parceiro_id'           => $ordensServico->first()->parceiro_id,
                'natureza_operacao'     => NaturezaOperacaoEnum::RETORNO_MERCADORIA,
                'notas_referenciadas'   => $notasRemessa,
                'status'                => StatusNotaFiscalEnum::PENDENTE,
            ]);

            $ordensServico->each(function ($ordem) use ($notaSaida) {
                return $ordem->notaRetorno()->attach($notaSaida->id, ['natureza_op' => NaturezaOperacaoEnum::RETORNO_MERCADORIA->value]);
            });

            return $notaSaida;
        });

    }

    private static function getNfeRemessa(Collection $ordensServico): array
    {
        return $ordensServico
            ->filter(fn($ordem) => $ordem->notaEntrada)
            ->map(fn($ordem) => [
                'chave_nota'    => $ordem->notaEntrada->chave_nota,
                'data_fatura'   => Carbon::parse($ordem->notaEntrada->data_fatura)->format('d/m/Y'),
                'nro_nota'      => $ordem->notaEntrada->nro_nota,
            ])
            ->unique('chave_nota')
            ->mapWithKeys(fn($nota) => [
                $nota['chave_nota'] => "Nro. {$nota['nro_nota']} - {$nota['data_fatura']}",
            ])
            ->toArray();

    }

    private static function validate(Collection $ordensServico)
    {

        if ($ordensServico->contains(fn ($ordem) => !$ordem->notaRetorno->isEmpty())) {
            throw new Exception('Existem ordens de serviço já vinculadas a uma nota de retorno.');
        }

        if ($ordensServico->unique('parceiro_id')->count() > 1) {
            throw new Exception('Todas as ordens de serviço devem pertencer ao mesmo cliente.');
        }

        if ($ordensServico->contains(fn ($ordem) => is_null($ordem->itemNotaRemessa))) {
            throw new Exception('Uma ou mais ordens de serviço não possuem vínculo com uma NF-e de remessa.');
        }

    }
}