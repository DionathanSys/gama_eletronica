<?php

namespace App\DTO\Fiscal;

use App\Contracts\NfeDTOInterface;
use App\DTO\Cliente\ClienteDTO;
use App\Enums\NaturezaOperacaoEnum;
use App\Models\NotaSaida;
use App\Models\Parceiro;
use App\Traits\ControleNumeracaoNf;
use Carbon\Carbon;

class NfeEstornoDTO implements NfeDTOInterface
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
        protected string    $informacoes_adicionais_fisco,
        protected array     $destinatario,
        protected array     $notas_referenciadas,
        protected array     $frete,
        protected array     $pagamento,
        protected           $itens,

    ) {}

    public static function fromNotaSaida(NotaSaida $notaSaida): self
    {
        $natureza_operacao                      = NaturezaOperacaoEnum::ESTORNO_NFE_NAO_CANCELADA->value;
        $tipo_operacao                          = 0;            // 0 - Entrada
        $serie                                  = config('nfe.serie.nfe_estorno');
        $numero                                 = self::getNextNumber($serie);
        $finalidade_emissao                     = 3;            // "3 - NFe de ajuste"
        $consumidor_final                       = 0;            // "0 - Normal" - "1 - Consumidor final"
        $presenca_comprador                     = 0;            // "0 - Não se aplica"
        $data_emissao                           = Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d\TH:i:sP');
        $data_entrada_saida                     = Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d\TH:i:sP');
        $informacoes_adicionais_contribuinte    = 'NFe 42250245790457000185550050000000321821022581 Nro. 32 Série 5';
        $informacoes_adicionais_fisco           = 'NFe estornada devido valor/quantidade incorretos, estando fora do prazo regulamentar para cancelamento.';
        $destinatario                           = (new ClienteDTO(Parceiro::find(46)))->toArray();

        $notas_referenciadas[]['nfe']['chave'] = '42250245790457000185550050000000321821022581';

        $frete = [
            'modalidade_frete'  => '9',
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
                'cfop'                      => '1949',
                'unidade_comercial'         => 'UN',
                'quantidade_comercial'      => 1,
                'valor_unitario_comercial'  => $ordem->itemNotaRemessa->valor,
                'valor_bruto'               => $ordem->itemNotaRemessa->valor,
                'inclui_no_total'           => 1,
                'imposto'                   => [
                    'icms'      => (object) ['situacao_tributaria' => 900],
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
            $informacoes_adicionais_fisco,
            $destinatario,
            $notas_referenciadas,
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
            'informacoes_adicionais_contribuinte'   => $this->informacoes_adicionais_contribuinte,
            'informacoes_adicionais_fisco'          => $this->informacoes_adicionais_fisco,
            'destinatario'          => $this->destinatario,
            'notas_referenciadas'   => $this->notas_referenciadas,
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
