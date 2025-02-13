<?php

namespace App\Filament\Resources\NotaSaidaResource\Pages;

use App\Actions\Fiscal\CancelarNfeAction;
use App\Actions\Fiscal\CreateNfeRetornoAction;
use App\Enums\StatusNotaFiscalEnum;
use App\Filament\Resources\NotaSaidaResource;
use App\Models\NotaSaida;
use App\Traits\Notifica;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;

class EditNotaSaida extends EditRecord
{
    use Notifica;

    protected static string $resource = NotaSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('confirmar')
                ->disabled(fn($record)=> $record->status != StatusNotaFiscalEnum::PENDENTE)
                ->action(function($record) {
                    $resp = (new CreateNfeRetornoAction())->execute($record, $this->data);
                    if ($resp) {
                        $this->notificaSucesso();
                        return redirect(NotaSaidaResource::getUrl('edit', ['record' => $record]));
                    }
                }),
            Actions\ActionGroup::make([
                Actions\Action::make('cancelar')
                    ->label('Cancelar NF-e')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalIconColor('danger')
                    ->modalAlignment(Alignment::Center)
                    ->visible(fn(NotaSaida $notaSaida) => $notaSaida->status == StatusNotaFiscalEnum::AUTORIZADA)
                    ->form([
                        Placeholder::make('alerta')
                            ->content('CERTIFIQUE-SE DE QUE O CANCELAMENTO ESTEJA DENTRO DO PRAZO!')
                            ->extraAttributes(['style' => 'padding: 10px; background-color: #fff3cd; color: #856404; font-weight: bold; border: 1px solid #ffeeba; border-radius: 5px;']),
                        Textarea::make('justificativa')
                            ->required()
                            ->default('CANCELAMENTO DE TESTE')
                            ->minLength(15)
                            ->maxLength(255),
                        TextInput::make('numero_validacao')
                            ->label('Nro. Validação')
                            ->default(rand(1999,5000)),
                        TextInput::make('numero_confirmacao')
                            ->label('Nro. Confirmação')
                            ->required(),
                        
                    ])
                    ->action(function (Action $action, NotaSaida $notaSaida, array $data) {
                        
                        if ($data['numero_validacao'] != $data['numero_confirmacao']) {
                            Notification::make()
                                ->title('Erro de validação')
                                ->body('Código de validação incorreto!')
                                ->icon('heroicon-o-exclamation-triangle')
                                ->iconColor('danger')
                                ->color('danger')
                                ->send();

                            $action->halt();
                        }

                        if ((new CancelarNfeAction)($notaSaida, $data)) {
                            Notification::make()
                                ->title('Solicitação concluída')
                                ->color('succes')
                                ->send();
                        }
                        
                        $this->refreshFormData([
                            'eventos', 'status',
                        ]);
                    })
            ])
            
        ];
    }

    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     dd ($data);
    //     return $data;
    // }

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     dd($data);
    //     return $data;
    // }
    // protected function getFormActions(): array
    // {
        
    //     if ($this->data['status'] != StatusNotaFiscalEnum::AUTORIZADA->value) {
    //         return [
    //             ...parent::getFormActions(),
    //         ];
    //     }

    //     return [];
    // }

    // protected function fillForm(): void
    // {
    //     dd($this->record);
    // }
}
