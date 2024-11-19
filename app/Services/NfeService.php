<?php

namespace App\Services;

use CloudDfe\SdkPHP\Nfe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class NfeService
{
    protected   Nfe    $nfe;
    protected   array   $params;
    
    public function __construct()
    {
        // $url = "https://hom-api.integranotas.com.br/v1/soft/emitente/45790457000185"; //Homologação
        // // $url = "https://api.integranotas.com.br/v1/soft/emitente/45790457000185";     //Produção

        // $dadosEmitente = Http::withToken(env('TOKEN_INTEGRA_NOTAS_SOFTHOUSE'))->get($url);

        // if ($dadosEmitente->emitente()->sucesso()){
        //     $token = $dadosEmitente()->token;
        // }

        $this->params = [
            "token" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbXAiOjIwMTcsInVzciI6NTAwLCJ0cCI6MiwiaWF0IjoxNzMxMDMzOTczfQ.B21Dp8XbkbnW7MTEAWrsi1yodnm810Bq70fNv0zKkmc',
            "ambiente" => 2,
            "options" => [
                "debug" => false,
                "timeout" => 60,
                "port" => 443,
                "http_version" => CURL_HTTP_VERSION_NONE
            ]
        ];
        
        $this->nfe = new Nfe($this->params);
    }
    public function cria($payload)
    {
        $resp = $this->nfe->cria($payload);
        return $resp;
    }

    public function consulta(string $chave)
    {
        $payload = [
            "chave" => $chave,
        ];

        return $this->nfe->pdf($payload);
    
        
    }

}