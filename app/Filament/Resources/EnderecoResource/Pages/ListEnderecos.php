<?php

namespace App\Filament\Resources\EnderecoResource\Pages;

use App\Filament\Resources\EnderecoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnderecos extends ListRecords
{
    protected static string $resource = EnderecoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Endereço'),
        ];
    }
}
