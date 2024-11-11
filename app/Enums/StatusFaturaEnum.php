<?php

namespace App\Enums;

enum StatusFaturaEnum:string
{
    case PENDENTE = 'PENDENTE';
    case CONFIRMADA = 'CONFIRMADA';

    public function getStatus ():string
    {
        return match ($this) {
            self::PENDENTE => 'PENDENTE',
            self::CONFIRMADA=> 'CONFIRMADA',
        };
    }
}
