<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Storage;

trait GerarXml
{
    public static function saveBase64ToXml(string $base64, $nomeArquivo = null): string
    {
        $xml = base64_decode($base64);

        if ($xml === false){
            throw new Exception("Erro ao gerar XML.");
        }

        $nomeArquivo = $nomeArquivo ?? 'doc_'.time().'.xml';

        $caminho = "xmls/{$nomeArquivo}";

        Storage::put($caminho, $xml);

        return $caminho;
    }
}