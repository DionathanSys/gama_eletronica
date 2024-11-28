<?php

namespace App\Traits;

use Filament\Notifications\Notification;

trait Notifica
{
    public static function notificaErro(string $body = null)
    {
        Notification::make()
            ->warning()
            ->title('Falha na solicitação.')
            ->body($body)
            ->send();
    }

    public static function notificaSucesso(string $body = null)
    {
        Notification::make()
            ->success()
            ->title('Falha na solicitação.')
            ->body($body)
            ->send();
    }
}