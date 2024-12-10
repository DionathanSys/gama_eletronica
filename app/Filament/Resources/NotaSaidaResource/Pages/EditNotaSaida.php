<?php

namespace App\Filament\Resources\NotaSaidaResource\Pages;

use App\Actions\Fiscal\CreateNfeRetornoAction;
use App\Enums\StatusNotaFiscalEnum;
use App\Filament\Resources\NotaSaidaResource;
use App\Traits\Notifica;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotaSaida extends EditRecord
{
    use Notifica;

    protected static string $resource = NotaSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('confirmar')
                ->disabled(fn($record)=>$record->status == StatusNotaFiscalEnum::AUTORIZADA->value)
                ->action(function($record) {
                    $resp = (new CreateNfeRetornoAction())->execute($record, $this->data);
                    if ($resp) {
                        $this->notificaSucesso();
                        return redirect(NotaSaidaResource::getUrl('edit', ['record' => $record]));
                    }
                })
        ];
    }

    protected function getFormActions(): array
    {
        
        if ($this->data['status'] != StatusNotaFiscalEnum::AUTORIZADA->value) {
            return [
                ...parent::getFormActions(),
            ];
        }

        return [];
    }
}
