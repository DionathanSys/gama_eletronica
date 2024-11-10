<?php

namespace App\Filament\Resources\FaturaResource\Pages;

use App\Filament\Resources\FaturaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFatura extends CreateRecord
{
    protected static string $resource = FaturaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        
        return $data;
    }
}
