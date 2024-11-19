<?php

namespace App\DTO\Servico;

use App\Models\Preferencia;
use Illuminate\Support\Facades\Session;

class ServicoDTO
{
    public $iss_retido = false;
    public $codigo;
    public $valor_nf;
    public $exigibilidade_iss;
    // public $responsavel_retencao;
    // public $codigo_municipio;
    // public $codigo_pais; 
    public $itens = [];

    public function __construct(float $valor_nf)
    {
        $config = Preferencia::where('descricao', 'Config. NFS')->get();

        $this->codigo = '1401';
        $this->iss_retido = $this->iss_retido;
        $this->exigibilidade_iss = 2;
        $this->valor_nf = $valor_nf;

    }

    /**
     * Converte o objeto ServicoDTO em um array, incluindo seus itens.
     *
     * @return array
     */
    public function toArray(string $discriminacao)
    {
        return [
            "iss_retido" => $this->iss_retido,
            "itens" => [[
                "codigo" => $this->codigo,
                "discriminacao" => $discriminacao,
                "exigibilidade_iss" => $this->exigibilidade_iss,
                "valor_servicos" => $this->valor_nf,
            ]],
            // "responsavel_retencao" => 421950,//$this->responsavel_retencao,
            // "codigo_municipio" => 421950,//$this->codigo_municipio,
            // "codigo_pais" => $this->codigo_pais,
        ];
    }
}