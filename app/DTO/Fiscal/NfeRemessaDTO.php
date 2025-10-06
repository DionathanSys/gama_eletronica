<?php

namespace App\DTO\Fiscal;

use App\Contracts\NfeDTOInterface;
use App\DTO\Cliente\ClienteDTO;
use App\Models\NotaSaida;
use App\Models\Parceiro;
use App\Traits\ControleNumeracaoNf;
use App\ValueObjects\Transportador;
use Carbon\Carbon;

class NfeRemessaDTO implements NfeDTOInterface
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
        protected array     $destinatario,
        protected array     $frete,
        protected array     $pagamento,
        protected           $itens,

    ) {}

    public static function fromNotaSaida(NotaSaida $notaSaida): self
    {
        $natureza_operacao                      = ($notaSaida->natureza_operacao)->value;
        $tipo_operacao                          = 1;
        $serie                                  = config('nfe.serie.nfe_remessa');
        $numero                                 = self::getNextNumber($serie);
        $finalidade_emissao                     = 1;            // 1 - Nota normal
        $consumidor_final                       = 0;            // "0 - Normal" - "1 - Consumidor final"
        $presenca_comprador                     = 0;            // "0 - Não se aplica"
        $data_emissao                           = Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d\TH:i:sP');
        $data_entrada_saida                     = Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d\TH:i:sP');
        $destinatario                           = (new ClienteDTO($notaSaida->parceiro))->toArray();

        $frete = $notaSaida->frete
            ? [
                'transportador'    => $notaSaida->frete[0]['data']['transportadora_id']
                    ? (new Transportador($notaSaida->frete[0]['data']['transportadora_id']))->toArray()
                    : null,
                'modalidade_frete' => $notaSaida->frete[0]['data']['modalidade_frete'],
                'volumes'          => [[
                    'especie'      => $notaSaida->frete[0]['data']['volume_especie'],
                    'quantidade'   => $notaSaida->frete[0]['data']['volume_quantidade'],
                    'peso_liquido' => $notaSaida->frete[0]['data']['volume_peso_liquido'],
                    'peso_bruto'   => $notaSaida->frete[0]['data']['volume_peso_bruto'],
                ]],
            ]
            : [
                'modalidade_frete' => 9
            ];

        $pagamento['formas_pagamento'][] = ['meio_pagamento' => 90, 'valor' => 0,];

        $itens = $notaSaida->itens;

        $i = 0;

        $itens = $itens->map(function ($item) use (&$i, $notaSaida) {
            return [
                'numero_item'               => ++$i,
                'codigo_produto'            => $item->codigo_produto,
                'origem'                    => 0,
                'descricao'                 => $item->descricao_produto,
                'codigo_ncm'                => $item->ncm,
                'cfop'                      => $item->cfop,
                'unidade_comercial'         => $item->unidade,
                'quantidade_comercial'      => $item->quantidade,
                'valor_unitario_comercial'  => $item->valor_unitario,
                'valor_bruto'               => (float) $item->quantidade * $item->valor_unitario,
                'inclui_no_total'           => 1,
                // 'imposto'                   => [
                //     'icms'      => (object) ['situacao_tributaria' => $item->impostos['icms']['situacao_tributaria']],
                //     'pis'       => (object) ['situacao_tributaria' => $item->impostos['pis']['situacao_tributaria']],
                //     'cofins'    => (object) ['situacao_tributaria' => $item->impostos['cofins']['situacao_tributaria']],
                // ],
                'imposto' => [
                    'icms'   => [
                        'situacao_tributaria' => $item->impostos['icms']['situacao_tributaria']
                    ],
                    'pis'    => [
                        'situacao_tributaria' => $item->impostos['pis']['situacao_tributaria']
                    ],
                    'cofins' => [
                        'situacao_tributaria' => $item->impostos['cofins']['situacao_tributaria']
                    ],
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
            $destinatario,
            $frete,
            $pagamento,
            $itens,
        );
    }

    public function toArray(): array
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
            'destinatario'          => $this->destinatario,
            'frete'                 => $this->frete,
            'pagamento'             => $this->pagamento,
            'itens'                 => $this->itens,
        ];
    }

    public function getNumero(): int
    {
        return $this->numero;
    }
    public function getSerie(): int
    {
        return $this->serie;
    }

    public function getDataEmissao(): string
    {
        return $this->data_emissao;
    }

    public function getDataEntradaSaida(): string
    {
        return $this->data_entrada_saida;
    }
}
