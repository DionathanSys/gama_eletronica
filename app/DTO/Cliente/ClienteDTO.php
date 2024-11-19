<?php

namespace App\DTO\Cliente;

use App\Models\Parceiro;

class ClienteDTO
{
    public $cpf;
    public $cnpj;
    public $im;
    public $razao_social;
    public $nome;
    public $telefone;
    public $email;
    public $endereco;
    public $inscricao_estadual;
    public $indicador_inscricao_estadual = 1;

    public function __construct(Parceiro $cliente)
    {
        $this->cpf = $cliente->tipo_documento == 'CPF' ? $cliente->nro_documento : null;
        $this->cnpj = $cliente->tipo_documento == 'CNPJ' ? $cliente->nro_documento : null;
        $this->im = $cliente->inscricao_municipal ?? '';
        $this->razao_social = $cliente->nome;
        $this->nome = $cliente->nome;
        $this->inscricao_estadual = $cliente->inscricao_estadual ?? null;
        $this->telefone = optional($cliente->contato)->telefone_cel 
                            ?? optional($cliente->contato)->telefone_fixo 
                            ?? '';
        $this->email = ($cliente->contato)->email ?? '';
        $this->endereco = (new EnderecoDTO(($cliente->enderecos)->first()))->toArray();
    }

    public function toArray()
    {
        return [
            "cnpj" => str_replace('/', '', str_replace('-', '', str_replace('.','',$this->cnpj))),
            "cpf" => str_replace('/', '', str_replace('-', '', str_replace('.','',$this->cpf))),
            "im" => $this->im,
            "razao_social" => $this->razao_social,
            "nome" => $this->nome,
            "telefone" => null,
            "email" => $this->email,
            "endereco" => $this->endereco,
            "inscricao_estadual" => $this->inscricao_estadual,
            "indicador_inscricao_estadual" => $this->indicador_inscricao_estadual,
        ];
    }
}
