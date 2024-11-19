<?php

namespace App\Enums;

enum NaturezaOperacaoEnum:string
{
    case VENDA_PRODUTO = 'Venda de Produto';
    case VENDA_SERVICO = 'Venda de Serviço';
    case REMESSA_CONSIGNACAO = 'Remessa em Consignação';
    case DEVOLUCAO_VENDA = 'Devolução de Venda';
    case RETORNO_MERCADORIA = 'Retorno de Mercadoria';

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
            self::VENDA_PRODUTO => 'Operação de venda de produtos.',
            self::VENDA_SERVICO => 'Prestação de serviços.',
            self::REMESSA_CONSIGNACAO => 'Envio de mercadorias para venda consignada.',
            self::DEVOLUCAO_VENDA => 'Devolução de produtos pelo cliente.',
            self::RETORNO_MERCADORIA => 'Retorno de mercadoria',
        };
    }
}
