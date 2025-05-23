<?php

namespace App\Traits;

use App\Models\NumeroNotaSaida;
use App\Models\Parceiro;

trait DefineCfop
{

    public static function getCfop(Parceiro $parceiro, string $tipo_nota): int
    {
        $operacao = $parceiro->endereco->estado == 'SC' ? 'intraestadual' : 'interestadual';
        return config("nfe.cfop.{$operacao}.{$tipo_nota}");
    }

}
