<?php

namespace App\Actions\Fiscal;

use App\Enums\StatusNotaFiscalEnum;
use App\Models\Documento;
use App\Models\NotaSaida;
use App\Models\User;
use App\Services\NfeService;
use App\Traits\GerarPdf;
use App\Traits\GerarXml;
use App\Traits\Notifica;
use Carbon\Carbon;
use CloudDfe\SdkPHP\Nfe;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CancelarNfeAction
{
    use Notifica, GerarPdf, GerarXml;

    protected NotaSaida     $notaSaida;
    protected NfeService    $Nfe;
    protected array         $data;

    public function __invoke(NotaSaida $notaSaida, array $data): bool
    {
        $this->notaSaida    = $notaSaida;
        $this->Nfe          = new NfeService();
        $this->data         = $data;

        if (! $this->validate()) {
            $this->notificaErro('Falha durante validação');
            return false;
        }

        if ($this->enviaCancelamento()) {
            $this->notificaErro('Falha durante solicitação');
            return false;
        }

        return true;

    }

    private function enviaCancelamento(): bool
    {
        if ($this->notaSaida->eventos) {
            $eventos = $this->notaSaida->eventos;
            $eventos[now()->timestamp] = 'SOLICITADO CANCELAMENTO';

        } else {
            $eventos[now()->timestamp] = 'SOLICITADO CANCELAMENTO';
        }

        $this->notaSaida->update([
            'eventos' => $eventos,
        ]);

        Log::debug(__METHOD__.' - '.__LINE__, [
            'nota_saida_id' => $this->notaSaida->id,
            'eventos' => $this->notaSaida->eventos,
            'mensagem' => 'Solicitando cancelamento',
        ]);

        $resp = $this->Nfe->cancelar($this->notaSaida, $this->data['justificativa']);

        if (! $resp->sucesso){
            Log::error(__METHOD__.' - '.__LINE__, [
                'mensagem' => 'Erro ao cancelar NFe',
                'resp'     => $resp,
            ]);
            Notification::make()
                ->title('Erro ao cancelar NFe')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase([User::find(1), User::find(2)]);

            return false;
        }

        Log::debug(__METHOD__.' - '.__LINE__, [
            'mensagem' => 'Cancelamento NFe realizado com sucesso',
            'nota_saida_id' => $this->notaSaida->id,
            'resp'     => $resp,
        ]);

        Documento::create([
            [
                'descricao' => 'PDF - CANCELAMENTO NFe',
                'path'      => $this->saveBase64ToPdf($resp->pdf),
            ],
            [
                'descricao' => 'XML - CANCELAMENTO NFe',
                'path'      => $this->saveBase64ToXml($resp->xml),
            ]
        ]);

        Log::debug(__METHOD__.' - '.__LINE__, [
            'nota_saida_id' => $this->notaSaida->id,
            'mensagem' => 'Documentos gerados com sucesso'

        ]);

        return true;
    }

    private function validate(): bool
    {

        if($this->notaSaida->status->value != StatusNotaFiscalEnum::AUTORIZADA->value){
            Log::error(__METHOD__.' - '.__LINE__, [
                'mensagem' => 'Não é possível cancelar NFe com status diferente de "Autorizada".',
                'status' => $this->notaSaida->status,
            ]);
            return false;
        }

        return true;
    }

}
