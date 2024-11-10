<?php

namespace App\Filament\Resources\FaturaResource\Pages;

use App\Filament\Resources\FaturaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFaturas extends ListRecords
{
    protected static string $resource = FaturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
