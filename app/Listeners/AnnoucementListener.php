<?php

namespace App\Listeners;

use App\Events\Announcement;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\PushChannel;

class AnnoucementListener implements ShouldQueue
{
    private $_pushChannel = '';
    private $_pusher = '';
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_pushChannel = new PushChannel();
        $this->_pusher = $this->_pushChannel->syndicatedPushConfig();
    }

    /**
     * Handle the event.
     *
     * @param  Announcement  $event
     * @return void
     */
    public function handle(Announcement $event)
    {
        $this->_pusher->trigger($event->params['channel'], $event->params['event'], $event->params);
    }
}
