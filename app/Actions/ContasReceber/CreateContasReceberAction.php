<?php

namespace App\Actions\Fatura;

use App\Models\Financeiro\ContaReceber;
use App\Models\Financeiro\Fatura;
use App\Models\Fiscal\Saida;
use Carbon\Carbon;

class CreateContasReceberAction
{

    public static function exec(Fatura $fatura)
    {
        $contas_receber = ContaReceber::create([
            'empresa_id' => 1,
            'parceiro_id' => $fatura->parceiro_id,
            'nota_saida_id' => $fatura->notas->first()->id,
            'data_vencimento' => Carbon::now()->addDays(15),
            'valor' => $fatura->valor_produtos + $fatura->valor_servicos,
            'desdobramento' => 1,
            'desdobramentos' => 1,
            'status' => 'Pendente',
        ]);

    }
}