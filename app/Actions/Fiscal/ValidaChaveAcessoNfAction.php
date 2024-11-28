<?php

namespace App\Actions\Fiscal;

use App\Models\NotaEntrada;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class ValidaChaveAcessoNfAction
{
    protected bool          $status = false;
    protected int           $serie;
    protected int           $nroNota;

    public function __construct(string $chaveAcesso)
    {
        if (Str::length($chaveAcesso) !== 44 || !is_numeric($chaveAcesso)) {
            $this->notificaErro();
        }

        $this->status = true;
        $this->serie = substr($chaveAcesso, 22, 3);
        $this->nroNota = substr($chaveAcesso, 25, 9);

    }

    public function getInfo(): object
    {
        return (object) [
            'status' => $this->status,
            'serie' => $this->serie ?? null,
            'nroNota' => $this->nroNota ?? null,
        ];
    }

    private function notificaErro(string $body = null)
    {
        Notification::make()
            ->warning()
            ->title('Falha durante solicitação')
            ->body($body)
            ->send();
    }
    
    private function notificaSucesso(string $body = null)
    {
        Notification::make()
            ->success()
            ->body($body)
            ->send();
    }
}