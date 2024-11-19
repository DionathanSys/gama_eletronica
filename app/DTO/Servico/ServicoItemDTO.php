<?php

namespace App\DTO\Servico;

use App\Models\Preferencia;

class ServicoItemDTO
{
    public $codigo;
    public $discriminacao;
    public $exigibilidade_iss = 2;
    public $codigo_cnae;
    public $codigo_tributacao_municipio;
    public $numero_processo;
    public $valor_servicos;
    public $valor_deducoes;
    public $valor_pis;
    public $valor_cofins;
    public $valor_inss;
    public $valor_ir;
    public $valor_csll;
    public $valor_outras;
    public $valor_iss;
    public $valor_aliquota;
    public $valor_desconto_condicionado;
    public $valor_desconto_incondicionado;

    public function __construct()
    {
    }
    /**
     * Converte o objeto ServicoItemDTO em um array.
     *
     * @return array
     */
    public function toArray(string $discriminacao, float $valor_nf)
    {
        return [
            "codigo" => $this->codigo,
            "discriminacao" => $discriminacao,
            "exigibilidade_iss" => $this->exigibilidade_iss,
            "valor_servicos" => $valor_nf, 
            // "codigo_cnae" => 1, //$this->codigo_cnae,
                // "codigo_tributacao_municipio" => 421950, //$this->codigo_tributacao_municipio,
                // "numero_processo" => 1, //$this->numero_processo,
                // "valor_deducoes" => 1, //$this->valor_deducoes,
                // "valor_pis" => 1, //$this->valor_pis,
                // "valor_cofins" => 1, //$this->valor_cofins,
                // "valor_inss" => 1, //$this->valor_inss,
                // "valor_ir" => 1, //$this->valor_ir,
                // "valor_csll" => 1, //$this->valor_csll,
                // "valor_outras" => 1, //$this->valor_outras,
                // "valor_iss" => 1, //$this->valor_iss,
                // "valor_aliquota" => 1, //$this->valor_aliquota,
                // "valor_desconto_condicionado" => 1, //$this->valor_desconto_condicionado,
                // "valor_desconto_incondicionado" => 1, //$this->valor_desconto_incondicionado
        ];
    }
}
