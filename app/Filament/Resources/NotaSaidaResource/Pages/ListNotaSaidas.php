<?php

namespace App\Filament\Resources\NotaSaidaResource\Pages;

use App\Filament\Resources\NotaSaidaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotaSaidas extends ListRecords
{
    protected static string $resource = NotaSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
