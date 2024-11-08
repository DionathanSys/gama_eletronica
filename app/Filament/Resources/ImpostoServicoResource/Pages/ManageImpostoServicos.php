<?php

namespace App\Filament\Resources\ImpostoServicoResource\Pages;

use App\Filament\Resources\ImpostoServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageImpostoServicos extends ManageRecords
{
    protected static string $resource = ImpostoServicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova Alíquota ISS'),
        ];
    }
}
