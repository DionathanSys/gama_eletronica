<?php

namespace App\Enums;

enum TipoManutencaoOrdemServicoEnum:string
{
    case CORRETIVA = 'CORRETIVA';
    case PREVENTIVA = 'PREVENTIVA';

    public function getStatus ():string
    {
        return match ($this) {
            self::CORRETIVA => 'CORRETIVA',
            self::PREVENTIVA => 'PREVENTIVA',
        };
    }
}
