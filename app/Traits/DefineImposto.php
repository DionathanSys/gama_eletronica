<?php

namespace App\Traits;

use App\Enums\NaturezaOperacaoEnum;
use App\Models\NumeroNotaSaida;
use App\Models\Parceiro;

trait DefineImposto
{

    public static function getImpostosDefault(): array
    {
        $impostos = [
            'icms' => [
                'situacao_tributaria' => config('nfe.icms.situacao_tributaria'),
            ],
            'pis' => [
                'situacao_tributaria' => config('nfe.pis.situacao_tributaria'),
            ],
            'cofins' => [
                'situacao_tributaria' => config('nfe.cofins.situacao_tributaria'),
            ],
        ];

        return $impostos;
    }

}
