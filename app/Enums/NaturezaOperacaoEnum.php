<?php

namespace App\Enums;

use App\DTO\Fiscal\NfeRetornoDTO;
use App\DTO\Fiscal\NfeEstornoDTO;
use App\DTO\Fiscal\NfeRemessaDTO;

enum NaturezaOperacaoEnum:string
{
    case VENDA_PRODUTO                  = 'VENDA DE PRODUTO';
    case VENDA_SERVICO                  = 'VENDA DE SERVIÇO';
    case REMESSA_CONSIGNACAO            = 'REMESSA DE MERCADORIA OU BEM PARA CONSERTO OU REPARO';
    case DEVOLUCAO_VENDA                = 'DEVOLUÇÃO DE VENDA';
    case RETORNO_MERCADORIA             = 'RETORNO DE MERCADORIA';
    case RETORNO_MERCADORIA_DEMO        = 'RETORNO DE MERCADORIA P/ DEMONSTRAÇÃO';
    case ESTORNO_NFE_NAO_CANCELADA      = 'ESTORNO NFE NÃO CANCELADA NO PRAZO LEGAL';

    /**
     * Obter todas as opções como um array associativo
     *
     * @return array
     */
    public static function getOptions(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    /**
     * Retornar a descrição de uma natureza específica
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::VENDA_PRODUTO                 => 'OPERAÇÃO DE VENDA DE PRODUTOS.',
            self::VENDA_SERVICO                 => 'PRESTAÇÃO DE SERVIÇOS.',
            self::REMESSA_CONSIGNACAO           => 'REMESSA DE MERCADORIA OU BEM PARA CONSERTO OU REPARO',
            self::DEVOLUCAO_VENDA               => 'DEVOLUÇÃO DE PRODUTOS PELO CLIENTE.',
            self::RETORNO_MERCADORIA            => 'RETORNO DE MERCADORIA',
            self::RETORNO_MERCADORIA_DEMO       => 'RETORNO DE MERCADORIA P/ DEMONSTRAÇÃO',
            self::ESTORNO_NFE_NAO_CANCELADA     => 'ESTORNO NFE NÃO CANCELADA NO PRAZO LEGAL',
        };
    }

    /**
     * Retorna a classe DTO apropriada para a natureza da operação
     *
     * @return string
     */
    public function getDTO(): string
    {
        return match ($this) {
            self::RETORNO_MERCADORIA        => NfeRetornoDTO::class,
            self::ESTORNO_NFE_NAO_CANCELADA => NfeEstornoDTO::class,
            self::REMESSA_CONSIGNACAO       => NfeRemessaDTO::class,
            self::RETORNO_MERCADORIA_DEMO   => NfeRemessaDTO::class,
        };
    }


}
