<?php

namespace App\Filament\Resources\ParceiroResource\Pages;

use App\Actions\Parceiro\CreateEnderecoAction;
use App\Filament\Resources\ParceiroResource;
use App\Models\Parceiro;
use App\Services\BuscaCNPJ;
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

    protected function afterCreate(): void
    {
        $parceiro = $this->record;
        (new CreateEnderecoAction($parceiro))->exec();
    }
}
