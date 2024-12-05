<?php

namespace App\Enums;

enum StatusProcessoOrdemServicoEnum:string
{
    case PENDENTE = 'PENDENTE';
    case EM_ATENDIMENTO = 'EM ATENDIMENTO';
    case AGUARDANDO_APROVACAO = 'AGUARDANDO APROVACAO';
    case ORCAMENTO_APROVADO = 'ORCAMENTO APROVADO';
    case ORCAMENTO_REPROVADO = 'ORCAMENTO REPROVADO';
    case CANCELADA = 'CANCELADA';
    case ENCERRADA = 'ENCERRADA';

    public function getStatus ():string
    {
        return match ($this) {
            self::PENDENTE => 'PENDENTE',
            self::EM_ATENDIMENTO => 'EM ATENDIMENTO',
            self::AGUARDANDO_APROVACAO => 'AGUARDANDO APROVACAO',
            self::ORCAMENTO_APROVADO => 'ORCAMENTO APROVADO',
            self::ORCAMENTO_REPROVADO => 'ORCAMENTO REPROVADO',
            self::CANCELADA => 'CANCELADA',
            self::ENCERRADA=> 'ENCERRADA',
        };
    }
}
