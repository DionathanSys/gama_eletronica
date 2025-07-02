<?php

namespace App\Filament\Resources\NotaSaidaResource\Pages;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\StatusNotaFiscalEnum;
use App\Filament\Resources\NotaSaidaResource;
use App\Filament\Resources\OrdemServicoResource;
use App\Models\NotaSaida;
use App\Services\NotaSaidaService;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;

class ListNotaSaidas extends ListRecords
{
    protected static string $resource = NotaSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Actions\Action::make('nova-nota-avulsa')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        OrdemServicoResource::getParceiroFormField(),
                        NotaSaidaResource::getNaturezaOperacaoFormField()
                            ->columnSpan(9)
                            ->disabled(false)
                            ->options([
                                NaturezaOperacaoEnum::REMESSA_CONSIGNACAO->value        => NaturezaOperacaoEnum::REMESSA_CONSIGNACAO->value,
                                NaturezaOperacaoEnum::RETORNO_MERCADORIA_DEMO->value    => NaturezaOperacaoEnum::RETORNO_MERCADORIA_DEMO->value,
                            ]),
                    ])
                    ->action(fn(array $data) => NotaSaidaService::criar(
                        $data['parceiro_id'],
                        NaturezaOperacaoEnum::tryFrom($data['natureza_operacao']),
                        StatusNotaFiscalEnum::PENDENTE
                    ))
                    ->label('Gerar NF-e Avulsa')
                    ->modalHeading('Gerar NF-e Avulsa')
                    ->modalDescription('Defina o parceiro para gerar a NF-e.')
                    ->modalSubmitActionLabel('Confirmar')
                    ->modalIcon('heroicon-o-document-text')
                    ->modalAlignment(Alignment::Center)
                    ->modalWidth(MaxWidth::Medium),
            ])
            ->iconbutton()
            ->color('info')
            ->size(ActionSize::ExtraLarge)
            ->icon('heroicon-o-bars-4'),
        ];
    }
}
