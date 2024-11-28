<?php

namespace App\Enums;

enum StatusProcessoOrdemServicoEnum:string
{
    case PENDENTE = 'PENDENTE';
    case EM_ATENDIMENTO = 'EM_ATENDIMENTO';
    case AGUARDANDO_APROVACAO = 'AGUARDANDO_APROVACAO';
    case ORCAMENTO_APROVADO = 'ORCAMENTO_APROVADO';
    case ORCAMENTO_REPROVADO = 'ORCAMENTO_REPROVADO';
    case CANCELADA = 'CANCELADA';
    case ENCERRADA = 'ENCERRADA';

    public function getStatus ():string
    {
        return match ($this) {
            self::PENDENTE => 'PENDENTE',
            self::EM_ATENDIMENTO => 'EM_ATENDIMENTO',
            self::AGUARDANDO_APROVACAO => 'AGUARDANDO_APROVACAO',
            self::ORCAMENTO_APROVADO => 'ORCAMENTO_APROVADO',
            self::ORCAMENTO_REPROVADO => 'ORCAMENTO_REPROVADO',
            self::CANCELADA => 'CANCELADA',
            self::ENCERRADA=> 'ENCERRADA',
        };
    }
}
