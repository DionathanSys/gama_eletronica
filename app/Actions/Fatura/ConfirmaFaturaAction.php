<?php

namespace App\Actions\Fatura;

use App\Enums\StatusContaReceberEnum;
use App\Enums\StatusFaturaEnum;
use App\Models\ContaReceber;
use App\Models\Fatura;
use Filament\Notifications\Notification;

class ConfirmaFaturaAction
{

    public function __construct(protected Fatura $fatura)
    {
    }

    public function exec()
    {
        if(! $this->validaContasReceber()){
            Notification::make()
                ->warning()
                ->title('Falha na solicitação')
                ->body('Divergência no Contas à Receber')
                ->send();

            return false;
        }

        $this->fatura->update([
            'status' => StatusFaturaEnum::CONFIRMADA,
        ]);

        $this->fatura->contasReceber->each(fn(ContaReceber $titulo) => $titulo->update(['status' => StatusContaReceberEnum::CONFIRMADA]));
        
        Notification::make()
                ->success()
                ->title('Fatura Confirmada')
                ->send();

        return true;
    }

    private function validaContasReceber(): bool
    {
        if($this->fatura->contasReceber->sum('valor') != $this->fatura->valor_total){
            return false;
        }

        return true;
    }
}