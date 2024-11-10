<?php

namespace App\Filament\Resources\ParceiroResource\Pages;

use App\Filament\Resources\ParceiroResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateParceiro extends CreateRecord
{
    protected static string $resource = ParceiroResource::class;

    protected static ?string $title = 'Novo Parceiro';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        
        return $data;
    }
}
