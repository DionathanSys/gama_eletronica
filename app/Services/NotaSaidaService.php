<?php

namespace App\Services;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\StatusNotaFiscalEnum;
use App\Filament\Resources\NotaSaidaResource;
use App\Models\NotaSaida;
use App\Models\Parceiro;
use App\Models\User;
use App\Traits\Notifica;
use Filament\Notifications\Notification;
use Filament\Notifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotaSaidaService
{
    use Notifica;

    public static function criar(int $cliente_id, NaturezaOperacaoEnum $naturezaOperacao)
    {
        $notaSaida = NotaSaida::create([
            'parceiro_id'       => $cliente_id,
            'natureza_operacao' => $naturezaOperacao,
            'status'            => StatusNotaFiscalEnum::PENDENTE,
        ]);

        Notification::make()
            ->title('NF-e criada com sucesso!')
            ->success()
            ->actions([
                Notifications\Actions\Action::make('abrir')
                    ->button()
                    ->url(NotaSaidaResource::getUrl('edit', ['record' => $notaSaida->id]))
                    ->openUrlInNewTab()
                    ->markAsUnread(),
            ])
            ->sendToDatabase(Auth::user());
    }

    public static function retornoAutorizacao(string $chave): void
    {
        $notaSaida = NotaSaida::where('chave_nota', $chave)->first();

        if (!$notaSaida) {
            Log::error('Nota de saída não encontrada', [
                'metodo'        => __CLASS__ . '::' . __METHOD__.' - ' . __LINE__,
                'chave_nota' => $chave,
            ]);
            return;
        }

        self::updateNota($notaSaida, [
            'status' => StatusNotaFiscalEnum::AUTORIZADA,
        ]);

        Log::info('Nota de saída atualizada', [
            'metodo'        => __METHOD__.' - ' . __LINE__,
            'chave_nota' => $chave,
        ]);

        Notification::make()
            ->title('Nota Fiscal Autorizada')
            ->body("NF-e chave: {$chave} foi autorizada com sucesso.")
            ->actions([
                Notifications\Actions\Action::make('Visualizar')
                    ->url(NotaSaidaResource::getUrl('edit', ['record' => $notaSaida->id]))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase(User::all());

        return;
    }

    public static function updateNota(NotaSaida|null $notaSaida, array $data): void
    {
        if (!$notaSaida) {
            $notaSaida = NotaSaida::where('chave_nota', $data['chave_nota'])->first();
        }

        $notaSaida->update($data);

        Log::debug('Nota de saída atualizada', [
            'nota_saida_id' => $notaSaida->id,
            'data'          => $data,
        ]);

        return;
    }

}

