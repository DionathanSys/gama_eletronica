<?php

namespace App\Actions\Fatura;

use App\Actions\Fatura\CreateContasReceberAction;
use App\Actions\NotaFiscalServico\RegistraNotas;
use App\Enums\StatusFaturaEnum;
use App\Models\ContaReceber;
use App\Models\Fatura;
use Illuminate\Support\Collection;

class CreateFaturaAction
{

    public static function exec(Collection $ordens):Fatura|false
    {
        //Verifica se existe mais de 01 cliente
        if(($ordens->unique('parceiro_id'))->count() > 1){
            return false;
        }
        
        //Valida se todas as ordens estão encerradas e ainda NÃO faturadas
        if (!$ordens->every(function($ordem){
            return 
                $ordem->status == 'Encerrada' &&
                $ordem->fatura_id == null;
        })){
            return false;
        }
        
        $parceiro_id = ($ordens->first())->parceiro_id;
    
        $valor_produtos = 0;
        $valor_servicos = 0;
        
        $ordens->each(function($ordem) use (&$valor_produtos, &$valor_servicos){
            
            if ($ordem->os_servicos){
                $valor_servicos+= $ordem->valor_total_servicos;
            }
            
            // if ($ordem->produtos){
            //     $valor_produtos+= $ordem->valor_total_produtos;
            // }

        });

        $fatura = (new Fatura())->create([
            'parceiro_id' => $parceiro_id,
            'valor_servicos' => $valor_servicos,
            'valor_produtos' => $valor_produtos,
            'status' => StatusFaturaEnum::PENDENTE,
        ]);
        
        $ordens->each(function ($ordem) use ($fatura) {
            $ordem->update([
                'fatura_id' => $fatura->id,
            ]);
        });

        (new RegistraNotas($fatura))->exec();

        CreateContasReceberAction::exec($fatura);

        return $fatura;
    }
}