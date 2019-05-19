<?php

namespace App\Listeners;

use App\Events\Announcement;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnnoucementListener implements ShouldQueue
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
     * @param  Announcement  $event
     * @return void
     */
    public function handle(Announcement $event)
    {
        //
    }
}
