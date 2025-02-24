<?php

namespace App\Listeners;

use App\Events\RetornoNfe;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotificarRetornoNfe
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RetornoNfe $event): void
    {
        
        Log::info($event);
    }
}
