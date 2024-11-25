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
            // ->setChromePath('/usr/bin/chromium-browser')
            ->noSandbox()
            ->setNodeBinary(env('AMBIENTE') == 'windows' ? 'C:\Program Files\nodejs\node.exe' : '/usr/bin/node') 
            ->setNpmBinary(env('AMBIENTE') == 'windows' ? 'C:\Program Files\nodejs\npm.cmd' : '/usr/bin/npm');

        // Browsershot::url('http://example.com')
        //     ->setOption('no-sandbox', true)
        //     ->setOption('args', ['--no-sandbox'])
        //     ->chromePath('/usr/bin/chromium-browser') // Caminho do Chromium no Ubuntu
        //     ->save('example.pdf');
    }
}
