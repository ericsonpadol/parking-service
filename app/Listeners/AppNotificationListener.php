<?php

namespace App\Listeners;

use App\Events\AppNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppNotificationListener
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
     * @param  AppNotification  $event
     * @return void
     */
    public function handle(AppNotification $event)
    {
        //
    }
}
