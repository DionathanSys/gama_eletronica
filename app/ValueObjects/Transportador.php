<?php

namespace App\ValueObjects;

use App\Models\Endereco;
use App\Models\Parceiro;
use InvalidArgumentException;

class Transportador
{
    public string | null $cnpj;
    public string | null $cpf;
    public string $nome;
    public string $inscricaoEstadual;
    public string $endereco;
    public string $nomeMunicipio;
    public string $uf;

    public function __construct(
        int $parceiro_id
    ) {
        
        $transportador = Parceiro::find($parceiro_id);
        
        if (! $transportador) {
            return false;
        }

        $this->cnpj = $transportador->tipo_documento == 'CNPJ' ? $transportador->nro_documento : null;
        $this->cpf = $transportador->tipo_documento == 'CPF' ? $transportador->nro_documento : null;
        $this->nome = $transportador->nome;
        $this->inscricaoEstadual = $transportador->inscricao_estadual;
        $this->endereco = $this->enderecoToString($transportador->enderecos->first());
        $this->nomeMunicipio = $transportador->enderecos->first()->cidade;
        $this->uf = strtoupper($transportador->enderecos->first()->estado);
    }

    public function __toString(): string
    {
        return "{$this->nome}, CNPJ: {$this->cnpj}, CPF: {$this->cpf}, Município: {$this->nomeMunicipio}/{$this->uf}";
    }

    public function toArray(): array
    {
        return [
            'cnpj' => $this->cnpj,
            'cpf' => $this->cpf,
            'nome' => $this->nome,
            'inscricao_estadual' => $this->inscricaoEstadual,
            'endereco' => $this->endereco,
            'nome_municipio' => $this->nomeMunicipio,
            'uf' => $this->uf,
        ];
    }

    private function enderecoToString(Endereco $endereco)
    {
        return "Rua {$endereco->rua} {$endereco->numero} {$endereco->complemento}, {$endereco->bairro}";
    }
}
