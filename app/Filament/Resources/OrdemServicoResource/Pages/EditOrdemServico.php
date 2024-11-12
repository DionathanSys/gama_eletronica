<?php

namespace App\Filament\Resources\OrdemServicoResource\Pages;

use App\Actions\Fatura\CreateFaturaAction;
use App\Actions\OrdemServico\UpdateStatusOrdemActions;
use App\Filament\Resources\FaturaResource;
use App\Filament\Resources\OrdemServicoResource;
use App\Models\OrdemServico;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditOrdemServico extends EditRecord
{
    protected static string $resource = OrdemServicoResource::class;

    protected static ?string $title = 'Editar Ordem';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('faturar')
                ->action(function(OrdemServico $record){
                    $fatura = CreateFaturaAction::exec(collect([$record]));
                    $this->refreshFormData([
                        'fatura_id'
                    ]);
                    if ($fatura){
                        return redirect(FaturaResource::getUrl('edit', ['record' => $fatura->id,]));
                    }
                }),

            Actions\Action::make('encerrar')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->action(function(OrdemServico $record) {
                    if ((new UpdateStatusOrdemActions($record))->encerrar()){
                        return redirect(OrdemServicoResource::getUrl('edit', ['record' => $this->getRecord()]));
                    }
                }),

            Actions\Action::make('reabrir')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->tooltip('Reabrir OS')
                ->label('')
                ->action(function(OrdemServico $record) {
                    if ((new UpdateStatusOrdemActions($record))->reabrir()){
                        return redirect(OrdemServicoResource::getUrl('edit', ['record' => $this->getRecord()]));
                    }
                }),

            Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->openUrlInNewTab()
                ->action(function(OrdemServico $record){
                    if($record->toPdf){
                        return redirect("/ordem-servico/{$record->id}/pdf");
                    }
                    Notification::make()
                        ->warning()
                        ->title('Solicitação não concluída!')
                        ->send();
                }),

            Actions\Action::make('email'),

            Actions\DeleteAction::make(),
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
