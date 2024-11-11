<?php

namespace App\Enums;

enum StatusFaturaEnum:string
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
