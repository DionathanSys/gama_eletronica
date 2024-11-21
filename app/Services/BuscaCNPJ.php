<?php

namespace App\Services;

use App\Models\Parceiro;

class BuscaCNPJ
{

    protected string $url;

    public function __construct(protected string $cnpj)
    {
        $this->url = 'https://publica.cnpj.ws/cnpj/'.str_replace(['.', '/', '-'], '', $cnpj);   
    }

    public function getInfo()
    {

        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);

        // Decodifica a resposta JSON
        $resp = json_decode($resp);
        
        return $resp->estabelecimento ?? null;

    }
}


        