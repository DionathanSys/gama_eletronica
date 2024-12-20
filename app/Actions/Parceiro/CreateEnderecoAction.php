<?php

namespace App\Actions\Parceiro;

use App\Models\Endereco;
use App\Models\Parceiro;
use App\Services\BuscaCNPJ;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateEnderecoAction
{
    public function __construct(protected Parceiro $parceiro)
    {
        
    }
    public function exec(): Endereco | false
    {
        $info_endereco = $this->getInfoCNPJ($this->parceiro);

        if ($info_endereco){
            $endereco = Endereco::create([
                            'parceiro_id' => $this->parceiro->id,
                            'rua' => Str::ascii($info_endereco->logradouro),
                            'numero' => $info_endereco->numero,
                            'complemento' => $info_endereco->complemento ?? null,
                            'bairro' => Str::ascii($info_endereco->bairro),
                            'codigo_municipio' => $info_endereco->cidade->ibge_id,
                            'cidade' => Str::ascii($info_endereco->cidade->nome),
                            'cep' => $info_endereco->cep,
                            'estado' => $info_endereco->estado->sigla,
                            'pais' => Str::ascii($info_endereco->pais->nome),
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),

                        ]);

            $ie = $this->parceiro->update([
                'inscricao_estadual' => $info_endereco->inscricoes_estaduais[0]->inscricao_estadual ?? null,
            ]);
                
            
            return $endereco ?? false;
        }
        
        return false;

    } 

    private function getInfoCNPJ()
    {
        return (new BuscaCNPJ($this->parceiro->nro_documento))->getInfo();
    }
}