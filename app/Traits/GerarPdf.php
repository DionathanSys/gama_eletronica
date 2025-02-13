<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Storage;

trait GerarPdf
{
    public static function saveBase64ToPdf(string $base64, $nomeArquivo = null): string
    {
        $pdf = base64_decode($base64);

        if ($pdf === false){
            throw new Exception("Erro ao gerar PDF.");
        }

        $nomeArquivo = $nomeArquivo ?? 'doc_'.time().'.pdf';

        $caminho = "pdfs/{$nomeArquivo}";

        Storage::put($caminho, $pdf);

        return $caminho;
    }
}