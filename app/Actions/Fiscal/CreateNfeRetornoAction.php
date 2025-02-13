<?php

namespace App\Actions\Fiscal;

use App\DTO\Fiscal\NfeDTO2;
use App\Enums\NaturezaOperacaoEnum;
use App\Enums\StatusNotaFiscalEnum;
use App\Models\NotaSaida;
use App\Models\User;
use App\Services\NfeService;
use App\Traits\{ControleNumeracaoNf, Notifica};
use CloudDfe\SdkPHP\Nfe;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CreateNfeRetornoAction
{
    use Notifica, ControleNumeracaoNf;

    protected NfeService           $nfeService;

    public function __construct() 
    {
        $this->nfeService = new NfeService();
    }

    public static function prepare(
        Collection  $ordensServico,
        array       $notas_referenciadas,
    ) {
      
        $ordensServico->load(['notaRetorno', 'itemNotaRemessa']);

        if (! $ordensServico->every(fn($ordemServico) => $ordemServico->notaRetorno->isEmpty())) {
            self::notificaErro('Encontrado ordens de serviço já vinculadas à uma nota de retorno, favor verificar');
            return false;
        }

        if (($ordensServico->unique('parceiro_id'))->count() > 1) {
            self::notificaErro('Multíplos clientes selecionados');
            return false;
        }

        if ($ordensServico->contains(fn($ordem) => $ordem->itemNotaRemessa === null)) {
            self::notificaErro('Ordem sem vinculo com NF-e de remessa');
            return false;
        }

        $data = [
            'parceiro_id' => $ordensServico[0]->parceiro_id,
            'natureza_operacao' => NaturezaOperacaoEnum::RETORNO_MERCADORIA->value,
            'notas_referenciadas' => $notas_referenciadas,
        ];

        $notaSaida = NotaSaida::create($data);

        $ordensServico->each(function ($ordemServico) use ($notaSaida) {
            return $ordemServico->notaRetorno()->attach($notaSaida->id, ['natureza_op' => NaturezaOperacaoEnum::RETORNO_MERCADORIA->value]);
        });

        return $notaSaida;
    }

    public function execute(NotaSaida $notaSaida, array $data)
    {   
        $notaFiscalDTO = NfeDTO2::fromMakeDto($notaSaida, $data);
        
        $resp = $this->nfeService->cria($notaFiscalDTO->toArray());
        
        if (! $resp->sucesso) {
            
            Notification::make()
                ->title('Falha na solicitação')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase([User::find(1), User::find(2)]);
            return false;
            
        }

        $this->setLastNumber($notaFiscalDTO->getNumero(), $notaFiscalDTO->getSerie());
        $this->setInfoRegistroNotaSaida($notaSaida, $notaFiscalDTO, $resp->chave);

        $this->notificaSucesso($notaFiscalDTO->getNumero(), $resp->chave);

        return $resp;
    }

    private function setInfoRegistroNotaSaida(NotaSaida $notaSaida, NfeDTO2 $dto, $chave)
    {

        $data = [
            'status' => StatusNotaFiscalEnum::AUTORIZADA->value,
            'chave_nota' => $chave,
            'nro_nota' => $dto->getNumero(),
            'serie' => $dto->getSerie(),
            'data_emissao' => $dto->getDataEmissao(),
            'data_entrada_saida' => $dto->getDataEntradaSaida(),
        ];

        $notaSaida->update($data);
    }

    private function notificaSucesso($nro_nota, $chave)
    {
        Notification::make()
            ->title('Sucesso')
            ->body('Documento emitido')
            ->body("NF-e Retorno Nº {$nro_nota}")
            ->actions([
                Action::make('Abrir')
                    ->button()
                    ->url(route('nfe.pdf', ['chave' => $chave]))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase([User::find(1), User::find(2)]);
    }
}
