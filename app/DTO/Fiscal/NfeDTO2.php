<?php

namespace App\DTO\Fiscal;

use App\DTO\Cliente\ClienteDTO;
use App\Models\NotaSaida;
use App\Models\Parceiro;
use App\Traits\ControleNumeracaoNf;
use App\ValueObjects\Transportador;
use Carbon\Carbon;

class NfeDTO2
{
    use ControleNumeracaoNf;

    public function __construct(
        protected string    $natureza_operacao,
        protected string    $tipo_operacao,
        protected string    $numero,
        protected string    $serie,
        protected string    $finalidade_emissao,
        protected string    $consumidor_final,
        protected string    $presenca_comprador,
        protected string    $data_emissao,
        protected string    $data_entrada_saida,
        protected string    $informacoes_adicionais_contribuinte,
        protected array     $destinatario,
        protected array     $notas_referenciadas,
        protected array     $frete,
        protected array     $pagamento,
        protected           $itens,

    ) {}

    public static function fromMakeDto(NotaSaida $notaSaida): self
    {
        $natureza_operacao                      = ($notaSaida->natureza_operacao)->value;
        $tipo_operacao                          = 1;
        $serie                                  = config('nfe.serie.nfe_retorno');
        $numero                                 = self::getNextNumber($serie);
        $finalidade_emissao                     = 1;            // 1 - Nota normal
        $consumidor_final                       = 1;            // "0 - Normal" - "1 - Consumidor final"
        $presenca_comprador                     = 0;            // "0 - Não se aplica"
        $data_emissao                           = Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d\TH:i:sP');
        $data_entrada_saida                     = Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d\TH:i:sP');
        $informacoes_adicionais_contribuinte    = 'Retorno de mercadoria ref. nota(s) ' . implode(', ', $notaSaida->notas_referenciadas);
        $destinatario                           = (new ClienteDTO(Parceiro::find($notaSaida->parceiro_id)))->toArray();

        foreach ($notaSaida->notas_referenciadas as $key => $value) {
            $notas_referenciadas[]['nfe']['chave'] = $key;
        }

        $frete = $notaSaida->frete[0]['data'];
        $frete = [
            'transportador'     => $frete['transportadora_id'] ? (new Transportador($frete['transportadora_id']))->toArray() : null,
            'modalidade_frete'  => $frete['modalidade_frete'],
            'volumes'           => [
                [
                    'especie'       => $frete['volume_especie'],
                    'quantidade'    => $frete['volume_quantidade'],
                    'peso_liquido'  => $frete['volume_peso_liquido'],
                    'peso_bruto'    => $frete['volume_peso_bruto'],
                ]
            ]
        ];

        $pagamento['formas_pagamento'][] = ['meio_pagamento' => 90, 'valor' => 0,];

        $ordensServico = $notaSaida->ordensServico;

        $i = 0;

        $itens = $ordensServico->map(function ($ordem) use (&$i, $notaSaida) {
            return [
                'numero_item'               => ++$i,
                'codigo_produto'            => $ordem->itemNotaRemessa->codigo_item,
                'origem'                    => 0,
                'descricao'                 => $ordem->equipamento->descricao,
                'codigo_ncm'                => $ordem->itemNotaRemessa->ncm_item,
                'cfop'                      => $notaSaida->parceiro->enderecos->first()->estado == 'SC' ? 5916 : 6916,
                'unidade_comercial'         => 'UN',
                'quantidade_comercial'      => 1,
                'valor_unitario_comercial'  => $ordem->itemNotaRemessa->valor,
                'valor_bruto'               => $ordem->itemNotaRemessa->valor,
                'inclui_no_total'           => 1,
                'imposto'                   => [
                    'icms'      => (object) ['situacao_tributaria' => 400],
                    'pis'       => (object) ['situacao_tributaria' => '08'],
                    'cofins'    => (object) ['situacao_tributaria' => '08'],
                ],  
            ];
        });

        return new self(
            $natureza_operacao,
            $tipo_operacao,
            $numero,
            $serie,
            $finalidade_emissao,
            $consumidor_final,
            $presenca_comprador,
            $data_emissao,
            $data_entrada_saida,
            $informacoes_adicionais_contribuinte,
            $destinatario,
            $notas_referenciadas,
            $frete,
            $pagamento,
            $itens,
        );
    }

    public function toArray()
    {
        return [
            'natureza_operacao'                     => $this->natureza_operacao,
            'tipo_operacao'                         => $this->tipo_operacao,
            'numero'                                => $this->numero,
            'serie'                                 => $this->serie,
            'finalidade_emissao'                    => $this->finalidade_emissao,
            'consumidor_final'                      => $this->consumidor_final,
            'presenca_comprador'                    => $this->presenca_comprador,
            'data_emissao'                          => $this->data_emissao,
            'data_entrada_saida'                    => $this->data_entrada_saida,
            'informacoes_adicionais_contribuinte'   => $this->informacoes_adicionais_contribuinte,
            'destinatario'          => $this->destinatario,
            'notas_referenciadas'   => $this->notas_referenciadas,
            'frete'                 => $this->frete,
            'pagamento'             => $this->pagamento,
            'itens'                 => $this->itens,
        ];
    }

    public function getNumero()
    {
        return $this->numero;
    }
    public function getSerie()
    {
        return $this->serie;
    }

    public function getDataEmissao()
    {
        return $this->data_emissao;
    }

    public function getDataEntradaSaida()
    {
        return $this->data_entrada_saida;
    }
}
