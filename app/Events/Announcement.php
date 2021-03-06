<?php

namespace App\Events;

use App\User;
use App\Message;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Announcement extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $params;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->params['event'] = 'message-blast';
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
