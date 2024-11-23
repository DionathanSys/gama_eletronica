<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\LaravelPdf\Facades\Pdf;

class Playground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:playground';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Pdf::html('<h1>Hello world!!</h1>')->save(storage_path('app\public\invoice2.pdf'));
    }
}
