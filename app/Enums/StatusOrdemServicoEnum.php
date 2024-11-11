<?php

namespace App\Enums;

enum StatusOrdemServicoEnum:string
{
    case PENDENTE = 'PENDENTE';
    case CANCELADA = 'CANCELADA';
    case ENCERRADA = 'ENCERRADA';

    public function getStatus ():string
    {
        return match ($this) {
            self::PENDENTE => 'PENDENTE',
            self::CANCELADA => 'CANCELADA',
            self::ENCERRADA=> 'ENCERRADA',
        };
    }
}
