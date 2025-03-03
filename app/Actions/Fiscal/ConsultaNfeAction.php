<?php

namespace App\Actions\Fiscal;

use App\Jobs\ConsultaNfJob;
use App\Models\User;
use App\Services\NfeService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class ConsultaNfeAction
{
    public static function execute(string $chave, int $tentativa): void
    {
        if ($tentativa > 5) {
            Notification::make()
                ->title('Falha na consulta')
                ->body("Atingido limite de tentativas para a NFe {$chave}.")
                ->sendToDatabase(User::all());
            return;
        }

        $nfe = new NfeService();
        
        $resp = $nfe->consulta($chave);

        if ($resp->codigo == 5023) {
            ConsultaNfJob::dispatch($chave, $tentativa + 1)->delay(now()->addSeconds(30));
            return;
        }

        if (!$resp->sucesso) {
            Notification::make()
                ->title('Falha na consulta')
                ->body("Código {$resp->codigo}: {$resp->mensagem}.")
                ->sendToDatabase(User::all());
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