<?php

namespace App\Services;

use CloudDfe\SdkPHP\Nfe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class NfeService
{
    protected   Nfe     $nfe;
    protected   array   $params;
    private     int     $ambiente = 2;
    private     string  $token;
    
    public function __construct()
    {

        if($this->ambiente == 1){
            //!Prod Gamma
             $this->token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbXAiOjkyOTgsInVzciI6MzI3LCJ0cCI6MiwiaWF0IjoxNzMyMDQ0OTYwfQ.t_-3jWNpPssWp101_aG2pFYdOkxPxmTVf0ZHBju0Msc';

        }else{
            //!Wp implemtno
            $this->token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbXAiOjE5NTIsInVzciI6NTAwLCJ0cCI6MiwiaWF0IjoxNzI3NTE3OTQ4fQ.OxOzaFpxkanEYrwXrVEQIGBQcFxidWWs9clnmC-m8kI';

        }
        $this->params = [

            "token" => $this->token,
            "ambiente" => $this->ambiente,
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
        return $this->nfe->cria($payload);
    }

    public function preview($payload)
    {
        return $this->nfe->preview($payload);
    }

    public function consulta(string $chave)
    {
        $payload = [
            "chave" => $chave,
        ];

        return $this->nfe->consulta($payload);
    
        
    }

    public function correcao($payload)
    {
        return $this->nfe->correcao($payload);
    }

    public function cancela($payload)
    {
        return $this->nfe->cancela($payload);
    }

    public function inutiliza($payload)
    {
        return $this->nfe->inutiliza($payload);
    }

}