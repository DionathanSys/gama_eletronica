<?php

namespace App\Filament\Resources\EnderecoResource\Pages;

use App\Filament\Resources\EnderecoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEndereco extends CreateRecord
{
    protected static string $resource = EnderecoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        
        return $data;
    }

}
