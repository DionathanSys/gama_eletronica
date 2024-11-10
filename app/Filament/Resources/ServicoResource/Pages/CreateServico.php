<?php

namespace App\Filament\Resources\ServicoResource\Pages;

use App\Filament\Resources\ServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateServico extends CreateRecord
{
    protected static string $resource = ServicoResource::class;

    protected static ?string $title = 'Novo Serviço';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        
        return $data;
    }
}
