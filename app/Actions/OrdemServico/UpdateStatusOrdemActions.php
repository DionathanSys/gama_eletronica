<?php

namespace App\Actions\OrdemServico;

use App\Enums\StatusOrdemServicoEnum;
use App\Models\OrdemServico;
use Filament\Notifications\Notification;

class UpdateStatusOrdemActions
{
    public function __construct(protected OrdemServico $ordemServico) {}

    public function encerrar(): bool
    {
        if ($this->ordemServico->status != StatusOrdemServicoEnum::PENDENTE->value || $this->ordemServico->itens->isEmpty()) {
            $this->notificaFalha('encerrar');
            return false;
        }

        $this->ordemServico->update([
            'status' => StatusOrdemServicoEnum::ENCERRADA
        ]);

        $this->notificaSucesso();

        return true;
    }

    public function reabrir() 
    {
        if ($this->ordemServico->status != StatusOrdemServicoEnum::ENCERRADA->value || $this->ordemServico->fatura_id != null){
            $this->notificaFalha('reabrir');
            return false;
        }

        $this->ordemServico->update([
            'status' => StatusOrdemServicoEnum::PENDENTE
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
