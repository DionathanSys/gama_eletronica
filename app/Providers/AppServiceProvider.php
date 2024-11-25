<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Spatie\Browsershot\Browsershot;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Browsershot::html('Foo')
            ->setOption('executablePath', '/usr/bin/chromium') // Substitua pelo caminho correto
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox']) // Evita problemas de permissões
            ->save(storage_path('app/public/ordem.pdf'));

        // Browsershot::url('http://example.com')
        //     ->setOption('no-sandbox', true)
        //     ->setOption('args', ['--no-sandbox'])
        //     ->chromePath('/usr/bin/chromium-browser') // Caminho do Chromium no Ubuntu
        //     ->save('example.pdf');
    }
}
