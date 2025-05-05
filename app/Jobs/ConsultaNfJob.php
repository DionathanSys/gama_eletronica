<?php

namespace App\Jobs;

use App\Actions\Fiscal\ConsultaNfeAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ConsultaNfJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $chave, protected int $tentativa = 1)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug('Job - Iniciando consulta de NF-e', [
            'chave' => $this->chave,
            'tentativa' => $this->tentativa,
        ]);
        ConsultaNfeAction::execute($this->chave, $this->tentativa);
    }
}
