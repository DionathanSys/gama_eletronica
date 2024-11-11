<?php

namespace App\Enums;

enum StatusOrdemServicoEnum:string
{
    case PENDENTE = 'Pendente';
    case CONFIRMADA = 'Confirmada';

    public function getStatus ():string
    {
        return match ($this) {
            self::PENDENTE => 'Pendente',
            self::CONFIRMADA=> 'Confirmada',
        };
    }
}
