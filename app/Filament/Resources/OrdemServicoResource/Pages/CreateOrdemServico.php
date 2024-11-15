<?php

namespace App\Filament\Resources\OrdemServicoResource\Pages;

use App\Enums\StatusOrdemServicoEnum;
use App\Filament\Resources\OrdemServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOrdemServico extends CreateRecord
{
    protected static string $resource = OrdemServicoResource::class;

    protected static ?string $title = 'Nova Ordem';

    // protected ?string $subheading = 'Custom Page Subheading';

    protected function mutateFormDataBeforeCreate(array $data): array
    {   
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['status'] = StatusOrdemServicoEnum::PENDENTE;
        
        return $data;
    }
}
