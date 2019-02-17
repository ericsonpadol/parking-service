<?php

use Illuminate\Database\Seeder;
use App\User;
use Faker\Factory as Faker;

class UserSeed extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $faker = Faker::create();

        /**
         * create user/seed for parking web connector
         */
        $options = [
            'cost' => '11',
        ];

        $activationSalt = md5('livesite');

        $userConnector = [
            'mobile_number' => '00000000000',
            'email' => 'parkit@gmail.com',
            'password' => password_hash('reject', PASSWORD_BCRYPT, $options),
            'full_name' => 'connector-admin',
            'is_activated' => 'true',
            'is_lock' => 'false'
        ];

        User::create($userConnector);

        for ($x = 0; $x < 6; $x++) {
            $seed = [
                'mobile_number' => str_pad($faker->randomNumber(), 11, '09', STR_PAD_LEFT),
                'email' => $faker->safeEmail,
                'password' => password_hash('secret', PASSWORD_BCRYPT, $options),
                'full_name' => $faker->name,
                'activation_token' => password_hash($faker->safeEmail, PASSWORD_BCRYPT)
            ];

            User::create($seed);
        }
    }

}