<?php

namespace App\Enums;

enum StatusContaReceberEnum:string
{
    case PENDENTE = 'PENDENTE';
    case CONFIRMADA = 'CONFIRMADA';
    case PAGO = 'PAGO';

    public function getStatus ():string
    {
        return match ($this) {
            self::PENDENTE => 'PENDENTE',
            self::CONFIRMADA=> 'CONFIRMADA',
            self::PAGO=> 'PAGO',
        };
    }
}
