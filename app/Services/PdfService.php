<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;

class PdfService
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

    public static function viewBase64ToPdf(string $base64)
    {
        $pdf = base64_decode($base64);

        if ($pdf === false){
            throw new Exception("Erro ao gerar PDF.");
        }

        return dd($pdf);
    }
}