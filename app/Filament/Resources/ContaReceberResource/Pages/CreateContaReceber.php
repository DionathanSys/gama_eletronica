<?php

namespace App\Filament\Resources\ContaReceberResource\Pages;

use App\Enums\StatusFaturaEnum;
use App\Filament\Resources\ContaReceberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateContaReceber extends CreateRecord
{
    protected static string $resource = ContaReceberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = StatusFaturaEnum::PENDENTE;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        
        return $data;
    }
}
