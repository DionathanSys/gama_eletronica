<?php

namespace App\Actions\Fiscal;

use App\Enums\StatusNotaFiscalEnum;
use App\Models\NotaSaida;
use App\Traits\GerarPdf;
use App\Traits\GerarXml;

class AtualizaDadosNfeAction
{
    use GerarPdf, GerarXml;

    public static function execute($resp): void
    {
        $notaSaida = NotaSaida::where('chave_nota', $resp->chave)->first();
        $notaSaida->update([
            'status' => StatusNotaFiscalEnum::AUTORIZADA,
        ]);
        
        $pathPdf = (new self)::saveBase64ToPdf($resp->pdf, $resp->chave);
        $pathXml = (new self)::saveBase64ToXml($resp->xml, $resp->chave);
      
        $notaSaida->documentos()->createMany([
            [
                'descricao' => 'DANFE NFe',
                'path'      => $pathPdf,
            ],
            [
                'descricao' => 'XML NFe',
                'path'      => $pathXml,
            ],
        ]);
    }
}