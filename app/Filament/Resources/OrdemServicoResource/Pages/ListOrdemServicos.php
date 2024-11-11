<?php

namespace App\Filament\Resources\OrdemServicoResource\Pages;

use App\Filament\Resources\OrdemServicoResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrdemServicos extends ListRecords
{
    protected static string $resource = OrdemServicoResource::class;

    protected static ?string $title = 'Ordens de Serviço';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova OS'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Pendente' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pendente')),
            'Encerradas' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'encerrada')
                                                                    ->where('fatura_id', '=', null)),
            'Faturadas' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'encerrada')
                                                                    ->where('fatura_id', '!=', null)),
            'Canceladas' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'canceladas')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'Pendente';
    }

}
