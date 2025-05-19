<?php

namespace App\Actions\Fiscal;

use App\Jobs\ConsultaNfJob;
use App\Models\User;
use App\Services\NfeService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ConsultaNfeAction
{
    public static function execute(string $chave, int $tentativa): void
    {
        Log::debug('ConsultaNfeAction', [
            'tentativa' => $tentativa,
            'chave'     => $chave,
        ]);
        
        if ($tentativa > 5) {
            Notification::make()
                ->title('Falha na consulta')
                ->body("Atingido limite de tentativas para a NFe {$chave}.")
                ->sendToDatabase(User::all());
            Log::alert("Atingido limite de tentativas para a NFe {$chave}.");
            return;
        }

        $nfe = new NfeService();

        $resp = $nfe->consulta($chave);

        if ($resp->codigo == 5023) {
            ConsultaNfJob::dispatch($chave, $tentativa + 1)->delay(now()->addSeconds(30));
            Log::debug('Job - Aguardando 30 segundos para nova consulta', [
                'tentativa' => $tentativa,
                'chave'     => $chave,
            ]);
            return;
        }

        if (!$resp->sucesso) {
            Notification::make()
                ->title('Falha na consulta')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase(User::all());
            Log::alert("Erro ao consultar NFe", [
                'chave' => $chave,
                'resp'  => $resp,
            ]);
            return;
        }

        AtualizaDadosNfeAction::execute($resp);

        Notification::make()
            ->title('Sucesso')
            ->body('Documento autorizado')
            ->body("NF-e Nro. {$resp->numero} - Série {$resp->serie}.")
            ->actions([
                Action::make('Abrir')
                    ->button()
                    ->url(route('nfe.pdf', ['chave' => $chave]))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase(User::all());
    }
}
