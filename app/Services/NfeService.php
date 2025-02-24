<?php

namespace App\Services;

use App\DTO\Fiscal\NfeDTO2;
use App\Enums\StatusNotaFiscalEnum;
use App\Models\Documento;
use App\Models\NotaSaida;
use App\Models\User;
use App\Traits\ControleNumeracaoNf;
use App\Traits\Notifica;
use CloudDfe\SdkPHP\Nfe;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class NfeService
{
    use Notifica, ControleNumeracaoNf;

    protected   Nfe     $nfe;
    protected   array   $params;
    private     int     $ambiente = 2;
    private     string  $token;

    public function __construct()
    {
        $this->nfe = new Nfe(config('nfe.params'));
    }

    public function cria($payload)
    {
        return $this->nfe->cria($payload);
    }

    public function criar(NotaSaida $notaSaida)
    {
        $notaFiscalDTO = NfeDTO2::fromMakeDto($notaSaida);
        
        $resp = $this->nfe->cria($notaFiscalDTO->toArray());

        if (!$resp->sucesso) {
            Notification::make()
                ->title('Falha na solicitação')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase([User::all()]);
            return false;
        }
        
        $this->setLastNumber($notaFiscalDTO->getNumero(), $notaFiscalDTO->getSerie());
        $this->refreshInfoNotaSaida($notaSaida, $notaFiscalDTO, $resp->chave);

        $this->notificaSucesso($notaFiscalDTO->getNumero(), $resp->chave);

        return $resp;

    }

    public function preview(NotaSaida $notaSaida)
    {
        $notaFiscalDTO = NfeDTO2::fromMakeDto($notaSaida);
        
        $resp = $this->nfe->preview($notaFiscalDTO->toArray());

        if (!$resp->sucesso) {
            Notification::make()
                ->title('Falha na solicitação')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase([User::all()]);
            return false;
        }

        return $resp;
    }

    public function consulta(string $chave)
    {
        $payload = [
            "chave" => $chave,
        ];

        return $this->nfe->consulta($payload);
    }

    public function correcao($payload)
    {
        return $this->nfe->correcao($payload);
    }

    public static function cancelar(NotaSaida $notaSaida, string $justificativa)
    {
        if ($notaSaida->status != StatusNotaFiscalEnum::AUTORIZADA) {
            (new self)->notificaErro("Não é possível cancelar NFe com status diferente de 'Autorizada'.");
            return false;
        }

        $payload = [
            'chave'         => $notaSaida->chave_nota,
            'justificativa' => $justificativa,
        ];

        $resp = (new self)->nfe->cancela($payload);

        if ($resp->sucesso) {
            $pathPdf = PdfService::saveBase64ToPdf($resp->pdf);
            $pathXml = XmlService::saveBase64ToXml($resp->xml);

            $notaSaida->documentos()->create(
                [
                    'descricao' => 'DANFE Cancelamento NFe',
                    'path'      => $pathPdf,
                ],
                [
                    'descricao' => 'XML Cancelamento NFe',
                    'path'      => $pathXml,
                ],
            );

            $notaSaida->status = StatusNotaFiscalEnum::CANCELADA;

            Notification::make()
                ->color('succes')
                ->title('Cancelamento NFe')
                ->body("Nro. Protocolo {$resp->protocolo}")
                ->sendToDatabase([User::all()]);
        }

        Notification::make()
            ->title('Falha durante solicitação!')
            ->body("Erro {$resp->codigo} - {$resp->mensagem}")
            ->sendToDatabase(User::all());

        return false;
    }

    public function inutiliza($payload)
    {
        return $this->nfe->inutiliza($payload);
    }

    private function refreshInfoNotaSaida(NotaSaida $notaSaida, NfeDTO2 $dto, $chave)
    {

        $data = [
            'status'                => StatusNotaFiscalEnum::AUTORIZADA->value,
            'chave_nota'            => $chave,
            'nro_nota'              => $dto->getNumero(),
            'serie'                 => $dto->getSerie(),
            'data_emissao'          => $dto->getDataEmissao(),
            'data_entrada_saida'    => $dto->getDataEntradaSaida(),
        ];

        $notaSaida->update($data);
    }

    private function notificaSucesso($nro_nota, $chave)
    {
        Notification::make()
            ->title('Sucesso')
            ->body('Documento encaminhado para autorização.')
            ->body("NF-e Retorno Nº {$nro_nota}")
            // ->actions([
                // Action::make('Abrir')
                //     ->button()
                //     ->url(route('nfe.pdf', ['chave' => $chave]))
                //     ->openUrlInNewTab(),
            // ])
            ->sendToDatabase(User::all());
        
    }
}
