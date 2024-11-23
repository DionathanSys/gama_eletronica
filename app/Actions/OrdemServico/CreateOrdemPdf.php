<?php

namespace App\Actions\OrdemServico;

use App\Models\OrdemServico;
use Spatie\LaravelPdf\Facades\Pdf;

class CreateOrdemPdf
{
    
    // public function __construct(OrdemServico $ordemServico)
    // {
    //     if (! $ordemServico->path_pdf){

    //     }
    // }

    // public function get()
    // {
    //     return $this->path;
    
    // }

    public function download()
    {

    }
}


namespace App\Actions;

use Barryvdh\DomPDF\Facade as PDF; // Ou a biblioteca de sua preferência
use App\Models\OrdemServico;

class GerarPdfOrdemServico
{
    
}
