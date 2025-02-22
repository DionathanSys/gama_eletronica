<?php

namespace App\Filament\Resources\NotaSaidaResource\Pages;

use App\Actions\Fiscal\CancelarNfeAction;
use App\Actions\Fiscal\CreateNfeRetornoAction;
use App\Enums\StatusNotaFiscalEnum;
use App\Filament\Resources\NotaSaidaResource;
use App\Models\NotaSaida;
use App\Models\Parceiro;
use App\Services\NfeService;
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

            Actions\ActionGroup::make([
                Actions\DeleteAction::make()
                    ->icon(null),
                Actions\Action::make('confirmar')
                    ->color('succes')
                    // ->disabled(fn($record) => $record->status != StatusNotaFiscalEnum::PENDENTE)
                    ->action(function ($record) {
                        $resp = (new CreateNfeRetornoAction())->execute($record, $this->data);
                        if ($resp) {
                            $this->notificaSucesso();
                            return redirect(NotaSaidaResource::getUrl('edit', ['record' => $record]));
                        }
                    }),
                Actions\Action::make('confirmar-teste')
                    ->label('Confirmar NFe')
                    ->color('info')
                    ->action(fn(NotaSaida $record) => (new NfeService())->criar($record)),
                Actions\Action::make('preview')
                    ->label('Preview NFe')
                    ->color('info')
                    ->url(fn(NotaSaida $record) => route('nfe.preview', ['notaSaida' => $record->id]))
                    ->openUrlInNewTab(),
                Actions\Action::make('pdf')
                    ->label('PDF')
                    ->color('info')
                    ->url(fn(NotaSaida $record) => route('nfe.view.pdf', ['notaSaida' => $record->id]))
                    ->openUrlInNewTab(),
                Actions\Action::make('cancelar')
                    ->color('danger')
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
                        TextInput::make('codigo_confirmation')
                            ->readOnly()
                            ->label('Nro. Validação')
                            ->default(rand(1999, 5000)),
                        TextInput::make('codigo')
                            ->label('Nro. Confirmação')
                            ->required(),

                    ])
                    ->action(function (Action $action, NotaSaida $notaSaida, array $data) {

                        if ($data['codigo'] != $data['codigo_confirmation']) {
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
                                ->color('success')
                                ->send();
                        }

                        $this->refreshFormData([
                            'eventos',
                            'status',
                        ]);
                    })
            ])->label('Ações')->button()

        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        //Devido à um preenchimento incorreto por parte do Form, onde ele assume para transportadora_id o mesmo valor de parceiro_id, quando
        //transportadora_id é null, o erro ocorre devido a utilização do mesmo relacionamento pelos dois

        if ($data['frete'][0]['data']['transportadora_id'] == $data['parceiro_id']) {
            $data['frete'][0]['data']['transportadora_id'] = null;
        }
        
        return $data;
    }
    
    
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
