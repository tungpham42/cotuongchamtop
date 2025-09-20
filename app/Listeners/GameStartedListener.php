<?php

namespace App\Listeners;

use App\Events\GameStarted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GameStartedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\GameStarted  $event
     * @return void
     */
    public function handle(GameStarted $event)
    {
        //
    }
}
