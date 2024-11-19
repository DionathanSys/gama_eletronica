<?php

namespace App\Services;

use App\Models\OrdemServico;
use CloudDfe\SdkPHP\Nfse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class NfeService
{
    protected   Nfse    $nfe;
    protected   array   $params;
    
    public function __construct()
    {
        $url = "https://hom-api.integranotas.com.br/v1/soft/emitente/45790457000185"; //Homologação
        // $url = "https://api.integranotas.com.br/v1/soft/emitente/45790457000185";     //Produção

        $dadosEmitente = Http::withToken(env('TOKEN_INTEGRA_NOTAS_SOFTHOUSE'))->get($url);

        if ($dadosEmitente->emitente()->sucesso()){
            $token = $dadosEmitente()->token;
        }

        $this->params = [
            "token" => $token,
            "ambiente" => 1,
            "options" => [
                "debug" => false,
                "timeout" => 60,
                "port" => 443,
                "http_version" => CURL_HTTP_VERSION_NONE
            ]
        ];
        
        $this->nfe = new Nfse($this->params);
    }
    public function cria(Collection $ordensServico)
    {
        
    }

    public function consulta(string $chave)
    {

    }

}