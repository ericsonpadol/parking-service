<?php

use Illuminate\Database\Seeder;
use App\PushChannel;
use Faker\Factory as Faker;

class PushChannelSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //create public channel seeds
        $publicChannels = [
            array(
                'channel_name' => 'parkit-main',
                'channel_type' => 'public',
                'ch_desc' => 'park-it main channel, for news and updates.'
            ),
            array(
                'channel_name' => 'parkit-taguig',
                'channel_type' => 'public',
                'ch_desc' => 'channel for taguig users.'
            ),
            array(
                'channel_name' => 'parkit-makati',
                'channel_type' => 'public',
                'ch_desc' => 'channel for makati users.'
            )
        ];

        foreach($publicChannels as $channel) {
            PushChannel::create($channel);
        }
    }
}
