<?php

namespace App\Traits;

use App\Models\OrdemServico;

trait UpdateStatusProcessoOrdemServico
{

    public static function updateStatusOrdemServico(OrdemServico $ordemServico, string $state)
    {
        $ordemServico->update([
            'status_processo' => $state,
        ]);
    }
}