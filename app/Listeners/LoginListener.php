<?php

namespace App\Listeners;

use App\Events\UserLogin;
use Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\PushChannel;

class LoginListener implements ShouldQueue
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
     * @param  UserLogin  $event
     * @return void
     */
    public function handle(UserLogin $event)
    {
        Log::info('user_login', ['user' => $event->user]);

        // $channels = $event->params['channels'];
        // foreach($channels['data'] as $channel) {
        //     var_dump($channel->channel_type);
        // }
        //  die();

        //trigger the public channel if a user login
        $this->_pusher->trigger('parkit-main', 'App\Events\UserLogin', $event->params);
    }
}
