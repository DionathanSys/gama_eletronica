<?php

namespace App\Enums;

enum StatusNotaFiscalEnum:string
{
    case PENDENTE = 'PENDENTE';
    case CANCELADA = 'CANCELADA';
    case REJEITADA = 'REJEITADA';
    case AUTORIZADA = 'AUTORIZADA';

    public function getStatus ():string
    {
        return match ($this) {
            self::PENDENTE => 'PENDENTE',
            self::CANCELADA => 'CANCELADA',
            self::REJEITADA=> 'REJEITADA',
            self::REJEITADA=> 'REJEITADA',
            self::AUTORIZADA=> 'AUTORIZADA',
        };
    }
}
