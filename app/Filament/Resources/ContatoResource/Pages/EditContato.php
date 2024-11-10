<?php

namespace App\Filament\Resources\ContatoResource\Pages;

use App\Filament\Resources\ContatoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditContato extends EditRecord
{
    protected static string $resource = ContatoResource::class;

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
