<?php

namespace App\Filament\Resources\EnderecoResource\Pages;

use App\Filament\Resources\EnderecoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEndereco extends EditRecord
{
    protected static string $resource = EnderecoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
