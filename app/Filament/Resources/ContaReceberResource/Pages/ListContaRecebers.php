<?php

namespace App\Filament\Resources\ContaReceberResource\Pages;

use App\Enums\StatusContaReceberEnum;
use App\Filament\Resources\ContaReceberResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListContaRecebers extends ListRecords
{
    protected static string $resource = ContaReceberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Pendente' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusContaReceberEnum::PENDENTE->value)),
            'Confirmada' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusContaReceberEnum::CONFIRMADA->value)),
            'Pago' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusContaReceberEnum::PAGO->value)),
      
            
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'Pendente';
    }
}
