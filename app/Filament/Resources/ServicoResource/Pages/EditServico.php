<?php

namespace App\Filament\Resources\ServicoResource\Pages;

use App\Filament\Resources\ServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditServico extends EditRecord
{
    protected static string $resource = ServicoResource::class;

    protected static ?string $title = 'Editar Serviço';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        
        return $data;
    }
}
