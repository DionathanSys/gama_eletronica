<?php

namespace App\Services;

use App\Contracts\NfeDTOInterface;
use App\DTO\Fiscal\NfeRetornoDTO;
use App\Enums\StatusNotaFiscalEnum;
use App\Jobs\ConsultaNfJob;
use App\Models\Documento;
use App\Models\NotaSaida;
use App\Models\User;
use App\Traits\ControleNumeracaoNf;
use App\Traits\Notifica;
use CloudDfe\SdkPHP\Nfe;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $dtoClass = $notaSaida->natureza_operacao->getDTO();
        /** @var NfeDTOInterface $dto */
        $dto = $dtoClass::fromNotaSaida($notaSaida);

        $resp = $this->nfe->cria($dto->toArray());

        if (!$resp->sucesso) {
            Notification::make()
                ->title('Falha na solicitação')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase(Auth::user());
            Log::alert("Erro ao criar NFe", [
                'nota_saida_id' => $notaSaida->id,
                'resp'          => $resp,
                'dto'           => $dto->toArray(),
            ]);
            return false;
        }

        $this->setLastNumber($dto->getNumero(), $dto->getSerie());
        $this->refreshInfoNotaSaida($notaSaida, $dto, $resp->chave);

        ConsultaNfJob::dispatch($resp->chave);

        $this->notificaSucesso($notaSaida, $dto->getNumero());

        return $resp;

    }

    public function preview(NotaSaida $notaSaida)
    {
        $dtoClass = $notaSaida->natureza_operacao->getDTO();
        /** @var NfeDTOInterface $dto */
        $dto = $dtoClass::fromNotaSaida($notaSaida);

        Log::debug('NfeService Gerando Preview NFe NF ID ' . $notaSaida->id, [
            'dto'           => $dto->toArray(),
        ]);

        $resp = $this->nfe->preview($dto->toArray());

        Log::debug('NfeService Preview NFe gerado NF ID' . $notaSaida->id, [
            'resp'          => $resp,
        ]);

        if (!$resp->sucesso) {
            Notification::make()
                ->title('Falha na solicitação')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase(User::all());

            return false;
        }

        return $resp;
    }

    public function consulta(string $chave)
    {
        $payload = [
            "chave" => $chave,
        ];

        Log::debug('NfeService Consultando NFe', [
            'chave' => $chave,
            'params' => config('nfe.params'),
        ]);

        $resp = $this->nfe->consulta($payload);

        if(!$resp->sucesso) {
            Notification::make()
                ->title('Falha na solicitação')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase(User::all());

            Log::error('Erro ao consultar NFe', [
                'chave' => $chave,
                'resp'  => $resp,
            ]);

            return false;
        }

        $notaSaida = NotaSaida::where('chave_nota', $chave)->first();

        if ($notaSaida) {
            $notaSaida->status = StatusNotaFiscalEnum::AUTORIZADA;
            $notaSaida->save();
        }

        return $resp;
    }

    public function correcao($payload)
    {
        return $this->nfe->correcao($payload);
    }

    public static function cancelar(NotaSaida $notaSaida, string $justificativa)
    {
        if ($notaSaida->status != StatusNotaFiscalEnum::AUTORIZADA) {

            Log::error('Tentativa de cancelamento de NFe com status diferente de autorizada', [
                'nota_saida_id' => $notaSaida->id,
                'status'        => $notaSaida->status,
            ]);

            (new self)->notificaErro("Não é possível cancelar NFe com status diferente de 'Autorizada'.");
            return false;
        }

        $payload = [
            'chave'         => $notaSaida->chave_nota,
            'justificativa' => $justificativa,
        ];

        $resp = (new self)->nfe->cancela($payload);

        if ($resp->sucesso) {
            Log::debug(__METHOD__.' - '.__LINE__, [
                'mensagem'       => 'Cancelamento NFe realizado com sucesso',
                'nota_saida_id' => $notaSaida->id,
                'resp'          => $resp,
            ]);

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

            Log::debug(__METHOD__.' - '.__LINE__, [
                'mensagem' => 'Atualizado nota de saída e criado documentos',
                'nota_saida_id' => $notaSaida->id,
                'status'        => $notaSaida->status,

            ]);

            Notification::make()
                ->color('succes')
                ->title('Cancelamento NFe')
                ->body("Nro. Protocolo {$resp->protocolo}")
                ->sendToDatabase(User::all());

            Log::debug(__METHOD__.' - '.__LINE__, [
                'mensagem' => 'notificado com sucesso',
                'nota_saida_id' => $notaSaida->id,
            ]);

            return $resp;
        }

        Log::error(__METHOD__.' - '.__LINE__, [
            'mensagem' => 'Erro ao cancelar NFe',
            'resp'     => $resp,
            'payload' => $payload,
        ]);

        Notification::make()
            ->title('Falha durante solicitação!')
            ->body("Erro {$resp->codigo} - {$resp->mensagem}")
            ->sendToDatabase(User::all());

        return $resp;
    }

    public function inutiliza($payload)
    {
        return $this->nfe->inutiliza($payload);
    }

    private function refreshInfoNotaSaida(NotaSaida $notaSaida, NfeDTOInterface $dto,string $chave): void
    {

        $data = [
            'chave_nota'            => $chave,
            'nro_nota'              => $dto->getNumero(),
            'serie'                 => $dto->getSerie(),
            'data_emissao'          => $dto->getDataEmissao(),
            'data_entrada_saida'    => $dto->getDataEntradaSaida(),
            'status'                => StatusNotaFiscalEnum::PROCESSANDO,
        ];

        Log::debug('Atualizando nota de saída', [
            'nota_saida_id' => $notaSaida->id,
            'data'          => $data,
        ]);

        $notaSaida->update($data);
    }

    private function notificaSucesso(NotaSaida $notaSaida, $nro_nota): void
    {
        Notification::make()
            ->title('Sucesso')
            ->body('Documento encaminhado para autorização.')
            ->body("NF-e Nº {$nro_nota}")
            ->actions([
                Action::make('Abrir')
                    ->button()
                    ->url(route('nfe.view.pdf', ['notaSaida' => $notaSaida]))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase(collect([Auth::user(), User::find(1)]));
    }
}
