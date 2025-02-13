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

        $resp = $this->Nfe->cancela([
            'justificativa'   =>  $this->data['justificativa'],
            'chave'           =>  '42250136286501000123550050000000221831090401'//$this->notaSaida->chave_nota,
        ]);

        if (! $resp->sucesso){

            Notification::make()
                ->title('Erro ao cancelar NFe')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase([User::find(1), User::find(2)]);

            return false;
        }

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

        return true;
    }

    private function validate(): bool
    {
        if($this->notaSaida->status->value != StatusNotaFiscalEnum::AUTORIZADA->value){
            return false;
        }

        return true;
    }

}