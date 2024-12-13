<?php

namespace App\Filament\Resources\OrdemServicoResource\Pages;

use App\Enums\StatusOrdemServicoEnum;
use App\Enums\StatusProcessoOrdemServicoEnum;
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
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_processo', StatusProcessoOrdemServicoEnum::PENDENTE->value)),
            'Em Atendimento' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_processo', StatusProcessoOrdemServicoEnum::EM_ATENDIMENTO->value)),
                                                                    
            'Encerrada' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusProcessoOrdemServicoEnum::ENCERRADA->value)
                                                                    ->where('fatura_id', '=', null)),

            'Cancelado' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_processo', StatusProcessoOrdemServicoEnum::CANCELADA->value)),
            
            'Orç. Reprovado' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_processo', StatusProcessoOrdemServicoEnum::ORCAMENTO_REPROVADO->value)),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'Pendente';
    }

}
