<?php

namespace App\Enums;

enum VinculoParceiroEnum:string
{
    case CLIENTE = 'Cliente';
    case COLABORADOR = 'Colaborador';
    case FORNECEDOR = 'Fornecedor';

    public function getVinculo ():string
    {
        return match ($this) {
            self::CLIENTE => 'Cliente',
            self::COLABORADOR => 'Colaborador',
            self::FORNECEDOR => 'Fornecedor',
        };
    }

}