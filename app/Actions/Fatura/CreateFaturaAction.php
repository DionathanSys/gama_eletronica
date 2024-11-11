<?php

namespace App\Actions\Fatura;

use App\Actions\Fatura\CreateContasReceberAction;
use App\Enums\StatusFaturaEnum;
use App\Models\ContaReceber;
use App\Models\Fatura;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class CreateFaturaAction
{

    public static function exec(Collection $ordens):Fatura|false
    {
        //Verifica se existe mais de 01 cliente
        if(($ordens->unique('parceiro_id'))->count() > 1){
            static::notificaFalha();
            return false;
        }
        
        //Valida se todas as ordens estão encerradas e ainda NÃO faturadas
        if (!$ordens->every(function($ordem){
                return 
                    $ordem->status == 'Encerrada' &&
                    $ordem->fatura_id == null;
                })){
                    static::notificaFalha();
                    return false;
        }
        
        $parceiro_id = ($ordens->first())->parceiro_id;
    
        $valor_servicos = $ordens->sum('valor_total');
        
        try {

            $fatura = (new Fatura())->create([
                'parceiro_id' => $parceiro_id,
                'valor_servicos' => $valor_servicos,
                'status' => StatusFaturaEnum::PENDENTE,
            ]);
            
            $ordens->each(function ($ordem) use ($fatura) {
                $ordem->update([
                    'fatura_id' => $fatura->id,
                ]);
            });

            (new RegistraNotas($fatura))->exec();

            CreateContasReceberAction::exec($fatura);
            
            static::notificaSucesso();
            return $fatura;

        } catch (\Throwable $e) {
            static::notificaFalha();
            return false;
        }

    }

    private static function notificaFalha()
    {
        Notification::make()
            ->danger()
            ->title('Solicitação não concluída!')
            ->send();
    }

    private static function notificaSucesso()
    {
        Notification::make()
            ->success()
            ->title('Fatura criada!')
            ->send();
    }
}