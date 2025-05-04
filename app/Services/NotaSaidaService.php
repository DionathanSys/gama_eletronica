<?php

namespace App\Services;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\StatusNotaFiscalEnum;
use App\Filament\Resources\NotaSaidaResource;
use App\Models\NotaSaida;
use App\Models\Parceiro;
use App\Traits\Notifica;
use Filament\Notifications\Notification;
use Filament\Notifications;
use Illuminate\Support\Facades\Auth;

class NotaSaidaService
{
    use Notifica;

    public static function criar(int $cliente_id, NaturezaOperacaoEnum $naturezaOperacao)
    {
        $notaSaida = NotaSaida::create([
            'parceiro_id' => $cliente_id,
            'natureza_operacao' => $naturezaOperacao,
            'status' => StatusNotaFiscalEnum::PENDENTE,
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

}

