<?php

namespace App\DTO\Cliente;

use App\Models\Endereco;

class EnderecoDTO
{
    public $logradouro;
    public $numero;
    public $complemento;
    public $bairro;
    public $nome_municipio;
    public $codigo_municipio;
    public $uf;
    public $nome_pais;
    public $codigo_pais;
    public $cep;

    public function __construct(Endereco $cliente)
    {
        $this->logradouro = $cliente->rua;
        $this->numero = $cliente->numero ?? '';
        $this->complemento = $cliente->complemento ?? '';
        $this->bairro = $cliente->bairro ?? '';
        $this->nome_municipio = $cliente->cidade;
        $this->codigo_municipio = $cliente->codigo_municipio;
        $this->uf = $cliente->estado;
        $this->nome_pais = null;
        $this->codigo_pais = null;
        $this->cep = $cliente->cep;
    }

    public function toArray()
    {
        return [
            "logradouro" => $this->logradouro,
            "numero" => $this->numero,
            "complemento" => $this->complemento,
            "bairro" => $this->bairro,
            "nome_municipio" => $this->nome_municipio,
            "codigo_municipio" => $this->codigo_municipio,
            "uf" => $this->uf,
            "codigo_pais" => $this->codigo_pais,
            "cep" => $this->cep
        ];
    }
}