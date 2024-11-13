<?php

namespace App\Filament\Resources\ContaReceberResource\Pages;

use App\Enums\StatusContaReceberEnum;
use App\Filament\Resources\ContaReceberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditContaReceber extends EditRecord
{
    protected static string $resource = ContaReceberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => $this->data['status'] == StatusContaReceberEnum::PENDENTE->value ? true : false),

            Actions\Action::make('pgto_pendente')
                ->label('Pgto. Pendente')
                ->icon('heroicon-o-banknotes')
                ->button()
                ->color('danger')
                ->action(function($record){
                    $record->update(['status' => StatusContaReceberEnum::CONFIRMADA]);
                    $this->refreshFormData(['data']);
                })
                ->visible(fn($record) => $record->status == StatusContaReceberEnum::PAGO->value ? true : false),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        
        return $data;
    }

    protected function getFormActions(): array
    {   

        if($this->data['status'] == 'pendente'){
            return [
                ...parent::getFormActions(),
            ];
        }
        
        return [];
    }
    
}
