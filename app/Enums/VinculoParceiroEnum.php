<?php

namespace App\Enums;

enum VinculoParceiroEnum:string
{
    case CLIENTE = 'CLIENTE';
    case COLABORADOR = 'COLABORADOR';
    case FORNECEDOR = 'FORNECEDOR';

    public function getVinculo ():string
    {
        return match ($this) {
            self::CLIENTE => 'CLIENTE',
            self::COLABORADOR => 'COLABORADOR',
            self::FORNECEDOR => 'FORNECEDOR',
        };
    }

}