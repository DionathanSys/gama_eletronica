<?php

namespace App\Filament\Resources\ServicoResource\Pages;

use App\Filament\Resources\ServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServicos extends ListRecords
{
    protected static string $resource = ServicoResource::class;

    protected static ?string $title = 'Serviços';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Serviço'),
        ];
    }
}
