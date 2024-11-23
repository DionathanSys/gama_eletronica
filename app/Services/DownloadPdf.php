<?php

namespace App\Services;

use function Spatie\LaravelPdf\Support\pdf;

class DownloadPdf
{
    public function __invoke(array $dados, string $view, $fileName)
    {
        $fileName = $fileName ?? now()->timestamp;

        return pdf()
            ->view($view, $dados)
            ->name($fileName.'.pdf')
            ->download();
    }
}
