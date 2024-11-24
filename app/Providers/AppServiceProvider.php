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
            ->setChromePath('/usr/local/bin/chromium-browser')
            ->setNodeBinary(env('AMBIENTE') == 'windows' ? 'C:\Program Files\nodejs\node.exe' : '/usr/local/bin/node') 
            ->setNpmBinary(env('AMBIENTE') == 'windows' ? 'C:\Program Files\nodejs\npm.cmd' : '/usr/local/bin/node');
    }
}
