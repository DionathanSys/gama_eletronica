<?php

namespace App\Filament\Resources\ParceiroResource\Pages;

use App\Filament\Resources\ParceiroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditParceiro extends EditRecord
{
    protected static string $resource = ParceiroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        
        return $data;
    }
}
