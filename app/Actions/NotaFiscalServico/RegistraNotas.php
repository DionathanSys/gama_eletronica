<?php

namespace App\Actions\Fatura;

use App\Models\Fatura;
use App\Models\Saida;
use App\Models\OsServico;
use Carbon\Carbon;

class RegistraNotas
{

    protected array $nfs = [];

    public function __construct(
        protected Fatura $fatura
    )
    {
        //Realiza o agrupamento das ordens por empresa
        $nfs_empresas = ($this->fatura->ordens_servico->groupBy('mo_empresa_id'))->all();

        foreach($nfs_empresas as $empresa_id => $ordens){

            $ordens_id = ($ordens->pluck('id'))->toArray();
            $servicos = (OsServico::whereIn('ordem_servico_id', $ordens_id)->get())->load('servico');

            $valor_total = $servicos->sum('valor_total');

            $this->nfs[$empresa_id] = [
                'empresa_id' => $empresa_id,
                'parceiro_id' => $this->fatura->parceiro->id,
                'fatura_id' => $fatura->id,
                'tipo_nota' => 'NFS',
                'data_fatura' => Carbon::createFromFormat('Y-m-d H:i:s', now())->format('Y-m-d'),
                'total' => $valor_total,
            ];
        }

    }

    public function exec()
    {
        foreach ($this->nfs as $nfs_e){
            Saida::create($nfs_e);
        }
    }
}