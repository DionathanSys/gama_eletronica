<?php

namespace App\Actions\Fiscal;

use App\DTO\Fiscal\NfeDTO;
use App\Enums\NaturezaOperacaoEnum;
use App\Models\Equipamento;
use App\Models\NotaEntrada;
use App\Models\OrdemServico;
use App\Models\Parceiro;
use App\Services\NfeService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection; 

class CreateNfRetornoAction
{
    protected Parceiro $cliente;
    protected array $notas_referenciadas;

    public function __construct(protected Collection $ordensServico)
    {
        $this->cliente = Parceiro::find($ordensServico->first()->parceiro_id);
    }

    public function exec() 
    {
        if (($this->ordensServico->unique('parceiro_id'))->count() > 1) {
            $this->notificaErro('Multíplos clientes selecionados');
            return false;
        }

        //Valida se as ordens ainda não tiveram vinculo com NF-e de Retorno
        if (!$this->ordensServico->every(fn($ordem) => $ordem->nota_retorno_id == null)) {
            $this->notificaErro('Ordem já está vinculada a uma NF-e de retorno');
            return false;
        }
        
        //Valida se as ordens possuem vinculo com NF-e de Remessa
        if (!$this->ordensServico->every(fn($ordem) => $ordem->nota_entrada_id != null)) {
            $this->notificaErro('Ordem sem vinculo com NF-e de remessa');
            return false;
        }

        $idNotasRemessa = $this->ordensServico
                            ->unique('nota_entrada_id')
                            ->pluck('nota_entrada_id')
                            ->toArray();

        $chaveNotasRemessa = NotaEntrada::whereIn('id', $idNotasRemessa)->pluck('chave_nota')->toArray();

        $idEquipamentos = $this->ordensServico->pluck('equipamento_id')->toArray();
        $equipamentos = Equipamento::whereIn('id', $idEquipamentos)->pluck('descricao')->toArray();
    
        $payload = new NfeDTO(
            $this->cliente, 
            $chaveNotasRemessa,
            $equipamentos,
            NaturezaOperacaoEnum::RETORNO_MERCADORIA->description(), 
        );

        dd($payload,$this->cliente, 
        $chaveNotasRemessa,
        $equipamentos,);
        // $resp = (new NfeService())->cria($payload->toArray());

        sleep(3);

        return $resp;
    }

    private function notificaErro($body = '')
    {
        Notification::make()
            ->warning()
            ->title('Solicitação não concluída')
            ->body($body)
            ->send();
    }
}
