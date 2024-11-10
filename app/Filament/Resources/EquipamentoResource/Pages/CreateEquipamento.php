<?php

namespace App\Filament\Resources\EquipamentoResource\Pages;

use App\Filament\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEquipamento extends CreateRecord
{
    protected static string $resource = EquipamentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        
        return $data;
    }
}
