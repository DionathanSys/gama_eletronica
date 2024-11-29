<?php

namespace App\DTO\Fiscal;

use App\DTO\Cliente\ClienteDTO;
use App\Models\NotaEntrada;
use App\Models\NumeroNotaSaida;
use App\Models\Parceiro;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
    public $informacoes_adicionais_contribuinte;


    public function __construct(
        Parceiro $cliente, 
        array $frete,
        array $notas_referenciadas, 
        Collection $ordensServico, 
        string $natureza_operacao
        )
    {
        $this->natureza_operacao = $natureza_operacao;
        $this->destinatario = (new ClienteDTO($cliente))->toArray();

        $nroNotaAtual = NumeroNotaSaida::where('serie_nota', 5)->max('nro_nota');

        $this->numero = $nroNotaAtual ? $nroNotaAtual + 1 : 1;;
        $this->serie = 5;

        $this->tipo_operacao = 1;
        $this->finalidade_emissao = 1;
        $this->consumidor_final = 1;
        $this->presenca_comprador = 0;

        foreach ($notas_referenciadas as $key => $value){
            $this->notas_referenciadas []['nfe']['chave'] = $value; 
        }

        $this->frete['modalidade_frete'] = $frete['modalidade_frete'];

        if(array_key_exists('transportadora', $frete)){
        
            $this->frete['transportador'] = [
                'cnpj' => $frete['transportadora']->nro_documento,
                'nome' =>$frete['transportadora']->nome,
                'inscricao_estadual' => $frete['transportadora']->inscricao_estadual,
                'endereco' => $frete['transportadora']->enderecos->first()->endereco,
                'nome_municipio' => $frete['transportadora']->enderecos->first()->cidade,
                'uf' =>$frete['transportadora']->enderecos->first()->estado,
            ];
        }

        $this->pagamento['formas_pagamento'] = array(['meio_pagamento' => 90, 'valor' => 0]);

        //---------------------------
        
        
        $descricaoNotas = [];

        foreach ($notas_referenciadas as $chave) {
            // Consultar a nota no banco de dados com base na chave
            $nota = NotaEntrada::where('chave_nota', $chave)->first();
            
            if ($nota) {
                // Formatar a descrição da nota com o número e a data_fatura
                $descricaoNotas[] = sprintf(
                    'NF-e %s - %s',
                    $nota->nro_nota,
                    \Carbon\Carbon::parse($nota->data_fatura)->format('d/m/Y')
                );
            }
        }
        
        //---------------------------

        $this->informacoes_adicionais_contribuinte = 'Retorno de mercadoria ref. ' . implode(', ', $descricaoNotas);

        $i = 0;
        $ordensServico->each(function($ordem) use(&$i, $cliente) {

            $this->itens[] = [
                'numero_item' => ++$i,
                'codigo_produto' => $ordem->itemNotaRemessa->codigo_item,
                'origem' => 0,
                'descricao' => $ordem->equipamento->descricao, 
                'codigo_ncm' => $ordem->itemNotaRemessa->ncm_item,
                'cfop' => $cliente->enderecos->first()->estado == 'SC' ? 5916 : 6916,
                'unidade_comercial' => 'UN',
                'quantidade_comercial' => 1, 
                'valor_unitario_comercial' => $ordem->itemNotaRemessa->valor, 
                'valor_bruto' => $ordem->itemNotaRemessa->valor, 
                'inclui_no_total' => 1,
                'imposto' => [
                    'icms' => (object) ['situacao_tributaria' => 400],
                    'pis' => (object) ['situacao_tributaria' => '08'],
                    'cofins' => (object) ['situacao_tributaria' => '08'],
                ],
            ];
        });

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
            "informacoes_adicionais_contribuinte" => $this->informacoes_adicionais_contribuinte,
        ];
    }
}


