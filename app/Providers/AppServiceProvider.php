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
                ->setNodeBinary('C:\Program Files\nodejs\node.exe') // Substitua pelo caminho correto
                ->setNpmBinary('C:\Program Files\nodejs\npm.cmd');  // Substitua pelo caminho correto
    }
}
