<?php

namespace App\Actions\ContasReceber;

use App\Enums\StatusFaturaEnum;
use App\Models\ContaReceber;
use App\Models\Fatura;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CreateContasReceberAction
{

    public static function exec(Fatura $fatura)
    {
        $contas_receber = ContaReceber::create([
            'parceiro_id' => $fatura->parceiro_id,
            'fatura_id' => $fatura->id,
            'data_vencimento' => Carbon::now()->addDays(15),
            'valor' => $fatura->valor_total,
            'desdobramento' => 1,
            'desdobramentos' => 1,
            'status' => StatusFaturaEnum::PENDENTE,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

    }
}