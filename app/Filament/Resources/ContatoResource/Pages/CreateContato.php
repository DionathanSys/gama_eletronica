<?php

namespace App\Filament\Resources\ContatoResource\Pages;

use App\Filament\Resources\ContatoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateContato extends CreateRecord
{
    protected static string $resource = ContatoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        
        return $data;
    }
}
