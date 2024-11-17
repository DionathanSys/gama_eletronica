<?php

namespace App\Filament\Resources\OrdemServicoResource\Pages;

use App\Actions\Fatura\CreateFaturaAction;
use App\Actions\OrdemServico\UpdateStatusOrdemActions;
use App\Enums\StatusOrdemServicoEnum;
use App\Filament\Resources\FaturaResource;
use App\Filament\Resources\OrdemServicoResource;
use App\Models\OrdemServico;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Auth;

class EditOrdemServico extends EditRecord
{
    protected static string $resource = OrdemServicoResource::class;

    protected static ?string $title = 'Editar Ordem';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\ActionGroup::make([
                    Actions\Action::make('pdf-os')
                        ->label('Ordem de Serviço')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->openUrlInNewTab()
                        ->action(function (OrdemServico $record) {
                            if ($record->toPdf) {
                                return redirect("/ordem-servico/{$record->id}/pdf");
                            }
                            Notification::make()
                                ->warning()
                                ->title('Solicitação não concluída!')
                                ->send();
                        }),

                    Actions\Action::make('pdf-orcamento')
                        ->label('Orçamento')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->openUrlInNewTab()
                        ->action(function (OrdemServico $record) {
                            if ($record->toPdf) {
                                return redirect("/ordem-servico/{$record->id}/orcamento/pdf");
                            }
                            Notification::make()
                                ->warning()
                                ->title('Solicitação não concluída!')
                                ->send();
                        }),
                ])->dropdown(false),

                Actions\ActionGroup::make([
                    Actions\Action::make('encerrar')
                        ->label('Encerar OS')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (OrdemServico $record) {
                            if ((new UpdateStatusOrdemActions($record))->encerrar()) {
                                return redirect(OrdemServicoResource::getUrl('edit', ['record' => $this->getRecord()]));
                            }
                        }),

                    Actions\Action::make('reabrir')
                        ->label('Reabrir OS')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('primary')
                        ->tooltip('Reabrir OS')
                        ->action(function (OrdemServico $record) {
                            if ((new UpdateStatusOrdemActions($record))->reabrir()) {
                                return redirect(OrdemServicoResource::getUrl('edit', ['record' => $this->getRecord()]));
                            }
                        }),
                    Actions\DeleteAction::make(),
                ])->dropdown(false),

                Actions\ActionGroup::make([
                    Actions\Action::make('faturar')
                        ->icon('heroicon-o-document-currency-dollar')
                        ->color('success')
                        ->action(function (OrdemServico $record) {
                            $fatura = CreateFaturaAction::exec(collect([$record]));
                            $this->refreshFormData([
                                'fatura_id'
                            ]);
                            if ($fatura) {
                                return redirect(FaturaResource::getUrl('edit', ['record' => $fatura->id,]));
                            }
                        }),
                    Actions\Action::make('email')
                        ->label('Enviar OS via Email')
                        ->color('info')
                        ->icon('heroicon-o-paper-airplane'),
                ])->dropdown(false),

            ])->label('Ações')->color('gray')->icon('heroicon-m-ellipsis-vertical')->button(),


        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getFormActions(): array
    {

        if ($this->data['status'] == StatusOrdemServicoEnum::PENDENTE->value) {
            return [
                ...parent::getFormActions(),
            ];
        }

        return [];
    }
}
