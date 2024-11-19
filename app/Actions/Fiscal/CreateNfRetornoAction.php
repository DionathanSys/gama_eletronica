<?php

namespace App\Fiscal;

use App\DTO\Fiscal\NfeDTO;
use App\Enums\NaturezaOperacaoEnum;
use App\Models\OrdemServico;
use App\Models\Parceiro;
use App\Services\NfeService;
use Illuminate\Database\Eloquent\Collection;

class CreateNfRetornoAction
{
    protected Parceiro $cliente;
    protected array $notas_referenciadas;

    public function __construct(protected Collection $ordensServico)
    {
        if (($ordensServico->unique('parceiro_id'))->count() > 1) {
            return false;
        }

        //Valida se as ordens ainda não tiveram vinculo com NF-e de Retorno
        if (!$ordensServico->every(fn($ordem) => $ordem->nota_retorno_id == null)) {
            return false;
        }
        
        //Valida se as ordens possuem vinculo com NF-e de Remessa
        if (!$ordensServico->every(fn($ordem) => $ordem->nota_entrada_id != null)) {
            return false;
        }

        $this->cliente = Parceiro::find($ordensServico->first()->parceiro_id);

        dump($ordensServico);
        dd($ordensServico->unique('nota_entrada_id'));

        $this->notas_referenciadas = $ordensServico;
    }

    public function exec() 
    {
        $payload = new NfeDTO(
            $this->cliente, NaturezaOperacaoEnum::RETORNO_MERCADORIA->description()
        );
        
        $resp = (new NfeService($payload));
    }
}
