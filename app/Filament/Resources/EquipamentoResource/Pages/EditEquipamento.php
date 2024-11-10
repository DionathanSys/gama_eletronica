<?php

namespace App\Filament\Resources\EquipamentoResource\Pages;

use App\Filament\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditEquipamento extends EditRecord
{
    protected static string $resource = EquipamentoResource::class;

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
