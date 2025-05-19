<?php

namespace App\Jobs;

use App\Services\NotaSaidaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AtualizaRetornoNotaFiscalJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $dados)
    {
        //
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
       try {
            NotaSaidaService::retornoAutorizacao($this->dados['chave']);
        } catch (\Exception $e) {
            Log::error('Erro ao executar Job', [
                'job'       => __CLASS__,
                'exception' => $e,
                'dados'     => $this->dados,
            ]);
        }

    }
}
