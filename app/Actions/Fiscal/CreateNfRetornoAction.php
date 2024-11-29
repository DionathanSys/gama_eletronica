<?php

namespace App\Actions\Fiscal;

use App\DTO\Fiscal\NfeDTO;
use App\Enums\NaturezaOperacaoEnum;
use App\Models\Equipamento;
use App\Models\NotaEntrada;
use App\Models\NumeroNotaSaida;
use App\Models\OrdemServico;
use App\Models\Parceiro;
use App\Services\NfeService;
use App\Traits\Notifica;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection; 

class CreateNfRetornoAction
{
    use Notifica;

    protected Parceiro  $cliente;
    protected Parceiro  $transportadora;
    protected array     $frete;
    protected array     $notas_referenciadas;

    public function __construct(protected Collection $ordensServico, array $data = null)
    {
        $this->cliente = Parceiro::find($ordensServico->first()->parceiro_id);

        $this->frete['modalidade_frete'] = $data['modalidade_frete'];
        
        if ($data['transportadora_id']) {
            $this->frete['transportadora'] = Parceiro::find($data['transportadora_id']);
        }

    }

    public function exec() 
    {   
        if (! $this->frete['modalidade_frete']) {
            $this->notificaErro('Modalidade de frete não informada');
            return false;
        }

        if (($this->ordensServico->unique('parceiro_id'))->count() > 1) {
            $this->notificaErro('Multíplos clientes selecionados');
            return false;
        }

        // //Valida se as ordens ainda não tiveram vinculo com NF-e de Retorno
        // if ($this->ordensServico->every(fn($ordem) => $ordem->nota_entrada_id != null)) {
        //     $this->notificaErro('Ordem já está vinculada a uma NF-e de retorno');
        //     return false;
        // }
        
        //Valida se as ordens possuem vinculo com NF-e de Remessa
        if ($this->ordensServico->every(fn($ordem) => $ordem->itemNotaRemessa == null)) {
            $this->notificaErro('Ordem sem vinculo com NF-e de remessa');
            return false;
        }
        
        $chavesNota = $this->ordensServico
                        ->map(fn($ordem) => $ordem->notaEntrada?->chave_nota) // Obter o campo 'chave_nota' do relacionamento
                        ->filter() // Remover valores nulos
                        ->unique() // Remover duplicados
                        ->values() // Reindexar a collection
                        ->toArray();
        
        $payload = new NfeDTO(
            $this->cliente, 
            $this->frete,
            $chavesNota,
            $this->ordensServico,
            NaturezaOperacaoEnum::RETORNO_MERCADORIA->description(), 
        );
        
        $resp = (new NfeService())->cria($payload->toArray());

        sleep(4);

        if ($resp->sucesso){

            $notaRemessa = NotaEntrada::create([
                'parceiro_id' => $this->ordensServico->first()->parceiro_id,
                'natureza_operacao' => NaturezaOperacaoEnum::RETORNO_MERCADORIA->description(),
                'chave_nota' => $resp->chave,
            ]);

            $this->ordensServico->each(function($ordem) use($notaRemessa) {
                $ordem->update([
                    'nota_entrada_id' => $notaRemessa->id,
                ]);
            });

            $chaveNotaRetorno = (new ValidaChaveAcessoNfAction($notaRemessa->chave_nota))->getInfo();

            NumeroNotaSaida::create([
                'nro_nota' => $chaveNotaRetorno->nroNota,
                'serie_nota' => $chaveNotaRetorno->serie,
            ]);

        }

        return $resp;
    }

    // private function notificaErro($body = '')
    // {
    //     Notification::make()
    //         ->warning()
    //         ->title('Solicitação não concluída')
    //         ->body($body)
    //         ->send();
    // }
}
