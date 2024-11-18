<?php

namespace App\Enums;

enum PrioridadeOrdemServicoEnum:string
{
    case BAIXA = 'BAIXA';
    case MEDIA = 'MEDIA';
    case ALTA = 'ALTA';
    case URGENTE = 'URGENTE';

    public function getStatus ():string
    {
        return match ($this) {
            self::BAIXA => 'BAIXA',
            self::MEDIA=> 'MEDIA',
            self::ALTA=> 'ALTA',
            self::URGENTE=> 'URGENTE',
        };
    }
}
