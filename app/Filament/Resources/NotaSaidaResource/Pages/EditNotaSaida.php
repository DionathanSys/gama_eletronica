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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class EditNotaSaida extends EditRecord
{
    use Notifica;

    protected static bool $processando = false;

    protected static string $resource = NotaSaidaResource::class;

    protected NfeService $nfeService;

    public function __construct()
    {
        $this->nfeService = new NfeService();
    }

    protected function getHeaderActions(): array
    {
        return [

            Actions\ActionGroup::make([
                Actions\DeleteAction::make()
                    ->visible(fn(NotaSaida $record) => $record->status == StatusNotaFiscalEnum::PENDENTE)
                    ->iconButton(),
                Actions\Action::make('confirmar')
                    ->label('Confirmar NFe')
                    ->color('info')
                    ->requiresConfirmation(fn() => env('AMBIENTE_NFE') == '1')
                    ->disabled(fn(NotaSaida $record) => $record->status != StatusNotaFiscalEnum::PENDENTE || self::$processando)
                    ->action(function(NotaSaida $record){
                        self::$processando = true;
                        $this->nfeService->criar($record);
                    }),
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

                        Log::debug(__METHOD__.' - '.__LINE__, [
                            'nota_saida_id' => $notaSaida->id,
                            'data' => $data,
                        ]);

                        if ($data['codigo'] != $data['codigo_confirmation']) {
                            Notification::make()
                                ->title('Erro de validação')
                                ->body('Código de validação incorreto!')
                                ->icon('heroicon-o-exclamation-triangle')
                                ->iconColor('danger')
                                ->color('danger')
                                ->send();

                            Log::error('Código de validação incorreto', [
                                'codigo' => $data['codigo'],
                                'codigo_confirmation' => $data['codigo_confirmation'],
                            ]);

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
                    }),
                Actions\Action::make('consultar')
                    ->label('Consultar NFe')
                    ->visible(fn(NotaSaida $record): bool => $record->status != StatusNotaFiscalEnum::PENDENTE)
                    ->color('info')
                    ->icon('heroicon-o-magnifying-glass')
                    ->action(fn (NotaSaida $record) => $this->nfeService->consulta($record->chave_nota)),
            ])->label('Ações')->button()

        ];
    }

    protected function getFormActions(): array
    {

        if (Auth::user()->admin || $this->data['status'] != StatusNotaFiscalEnum::AUTORIZADA->value) {
            return [
                ...parent::getFormActions(),
            ];
        }

        return [];
    }

}
