<?php

namespace App\Actions\OrdemServico;

use App\Models\OrdemServico;
use Filament\Notifications\Notification;

class UpdateStatusOrdemActions
{
    public function __construct(protected OrdemServico $ordemServico) {}

    public function encerrar(): bool
    {
        if ($this->ordemServico->status != 'pendente' || $this->ordemServico->itens->isEmpty()) {
            $this->notificaFalha('encerrar');
            return false;
        }

        $this->ordemServico->update([
            'status' => 'encerrada'
        ]);

        $this->notificaSucesso();

        return true;
    }

    public function reabrir() 
    {
        if ($this->ordemServico->status != 'encerrada' || $this->ordemServico->fatura_id != null){
            $this->notificaFalha('reabrir');
            return false;
        }

        $this->ordemServico->update([
            'status' => 'pendente'
        ]);

        $this->notificaSucesso();

        return true;

    }

    private function notificaFalha($action)
    {
        Notification::make()
            ->danger()
            ->title('Solicitação não concluída!')
            ->body("Falha ao {$action} ordem de serviço!")
            ->send();
    }

    private function notificaSucesso()
    {
        Notification::make()
            ->success()
            ->title("Solicitação concluída!")
            ->send();
    }
}
