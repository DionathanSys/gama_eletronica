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
                Actions\Action::make('nova-nota-remessa')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        OrdemServicoResource::getParceiroFormField()
                    ])
                    ->action(fn(array $data) => NotaSaidaService::criar(
                        $data['parceiro_id'],
                        NaturezaOperacaoEnum::REMESSA_CONSIGNACAO,
                        StatusNotaFiscalEnum::PENDENTE
                    ))
                    ->label('Gerar NF-e Remessa')
                    ->modalHeading('Gerar NF-e Remessa')
                    ->modalDescription('Defina o parceiro para gerar a NF-e de remessa.')
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
