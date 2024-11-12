<?php

namespace App\Filament\Resources\FaturaResource\Pages;

use App\Enums\StatusFaturaEnum;
use App\Filament\Resources\FaturaResource;
use App\Models\Fatura;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditFatura extends EditRecord
{
    protected static string $resource = FaturaResource::class;

    protected static ?string $title = 'Fatura';

    protected function getHeaderActions(): array
    {
        if ($this->data['status'] == StatusFaturaEnum::PENDENTE->value){
            return [
                Actions\DeleteAction::make(),
                Actions\Action::make('confirmar')
                    ->action(function(Fatura $record) {
                        $record->update(['status' => StatusFaturaEnum::CONFIRMADA]);
                        return redirect(FaturaResource::getUrl('edit', ['record' => $record->id]));
                    }),
            ];
        }

        return [];
        
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        
        return $data;
    }

    protected function getFormActions(): array
    {   
        return [];
    }
}
