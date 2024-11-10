<?php

namespace App\Filament\Resources\ServicoResource\Pages;

use App\Filament\Resources\ServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListServicos extends ListRecords
{
    protected static string $resource = ServicoResource::class;

    protected static ?string $title = 'Serviços';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Serviço')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = Auth::id();
                    $data['updated_by'] = Auth::id();
             
                    return $data;
                }),
        ];
    }
}
