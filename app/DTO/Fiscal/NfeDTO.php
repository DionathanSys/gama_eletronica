<?php

namespace App\DTO\Fiscal;

use App\DTO\Cliente\ClienteDTO;
use App\Models\Parceiro;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class NfeDTO
{
    public      $natureza_operacao;
    protected   $numero;
    protected   $serie;
    public      $data_emissao;
    public      $data_entrada_saida;
    public      $tipo_operacao = [
                    0 => '0 - Nota de entrada', 
                    1 => '1 - Nota de saída'
                ];
    public      $finalidade_emissao = [
                    1 => "1 - Nota normal",
                    2 => "2 - Nota complementar",
                    3 => "3 - Nota de ajuste",
                    4 => "4 - Devolução de mercadoria"
                ];
    public      $consumidor_final = [
                    0 => 'Normal',
                    1 => 'Consumidor final',
                ];
    public $presenca_comprador;
    public $intermediario;
    public $notas_referenciadas;
    public $destinatario;
    public $tomador;
    
    public $itens;

    public $frete;
    public $pagamento;


    public function __construct(Parceiro $cliente, array $notas_referenciadas, array $itens, string $natureza_operacao)
    {
        $this->natureza_operacao = $natureza_operacao;
        $this->destinatario = (new ClienteDTO($cliente))->toArray();

        $this->numero = 4;
        $this->serie = 1;

        $this->tipo_operacao = 1;
        $this->finalidade_emissao = 1;
        $this->consumidor_final = 1;
        $this->presenca_comprador = 0;

        foreach ($notas_referenciadas as $key => $value){
            $this->notas_referenciadas []['nfe']['chave'] = $value; 
        }

        $this->frete = [
            'modalidade_frete' => 1,
        ];

        $this->pagamento['formas_pagamento'] = array(['meio_pagamento' => 90, 'valor' => 0]);

        foreach ($itens as $key => $value){

            $this->itens[] = [
                'numero_item' => $key + 1,
                'codigo_produto' => $key + 1, // Ajuste conforme necessário
                'origem' => 0,
                'descricao' => 0, // Ajuste conforme necessário
                'codigo_ncm' => '85389010',
                'cfop' => 5916,
                'unidade_comercial' => 'UN',
                'quantidade_comercial' => 1, // Ajuste conforme necessário
                'valor_unitario_comercial' => 0, // Ajuste conforme necessário
                'valor_bruto' => 0, // Ajuste conforme necessário
                'inclui_no_total' => 1,
                'imposto' => [
                    'icms' => (object) ['situacao_tributaria' => 400],
                    'pis' => (object) ['situacao_tributaria' => '08'],
                    'cofins' => (object) ['situacao_tributaria' => '08'],
                ],
            ];
        }   

        // $this->itens = [$this->itens];

    }

    public function toArray()
    {   
        return [
            "natureza_operacao" => $this->natureza_operacao,
            "destinatario" => $this->destinatario,
            "numero" => $this->numero,
            "serie" => $this->serie,
            "data_emissao" => Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d\TH:i:sP'),
            "data_competencia" => Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d\TH:i:sP'),
            "tipo_operacao" => $this->tipo_operacao,
            "finalidade_emissao" => $this->finalidade_emissao,
            "consumidor_final" => $this->consumidor_final,
            "presenca_comprador" => $this->presenca_comprador,
            "notas_referenciadas" => $this->notas_referenciadas,
            "frete" => $this->frete,
            "pagamento" => $this->pagamento,
            "itens" => $this->itens,
        ];
    }
}


