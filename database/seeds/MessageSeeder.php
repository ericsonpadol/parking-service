<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Message;
use Illuminate\Support\Facades\DB;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $faker = Faker::create();
        $limitBreak = 500000;
        $messageType = [
            'blast',
            'incoming',
            'outgoing'
        ];

        for ($x = 0; $x < $limitBreak; $x++) {
            $toUserId = ($faker->numberBetween(1, 21) * 10) + 1;
            $fromUserId = ($faker->numberBetween(1, 21) * 10) + 1;
            $seedMessage = [
                'to_user_id' => $toUserId,
                'from_user_id' => $fromUserId,
                'message_type' => $messageType[array_rand($messageType, 1)],
                'message' => $faker->text(140)
            ];

            $lastMsgId = Message::create($seedMessage)->id;
            $seedMessage = [
                'message_id' => $lastMsgId,
                'to_user_id' => $toUserId,
                'message_status' => 'unread'
            ];

            DB::table('messages_status')->insert($seedMessage);
        }
    }
}
