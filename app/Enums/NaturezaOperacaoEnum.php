<?php

namespace App\Enums;

enum NaturezaOperacaoEnum:string
{
    case VENDA_PRODUTO                  = 'VENDA DE PRODUTO';
    case VENDA_SERVICO                  = 'VENDA DE SERVIÇO';
    case REMESSA_CONSIGNACAO            = 'REMESSA DE MERCADORIA OU BEM PARA CONSERTO OU REPARO';
    case DEVOLUCAO_VENDA                = 'DEVOLUÇÃO DE VENDA';
    case RETORNO_MERCADORIA             = 'RETORNO DE MERCADORIA';
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
            self::VENDA_PRODUTO         => 'OPERAÇÃO DE VENDA DE PRODUTOS.',
            self::VENDA_SERVICO         => 'PRESTAÇÃO DE SERVIÇOS.',
            self::REMESSA_CONSIGNACAO   => 'REMESSA DE MERCADORIA OU BEM PARA CONSERTO OU REPARO',
            self::DEVOLUCAO_VENDA       => 'DEVOLUÇÃO DE PRODUTOS PELO CLIENTE.',
            self::RETORNO_MERCADORIA    => 'RETORNO DE MERCADORIA',
        };
    }
}
