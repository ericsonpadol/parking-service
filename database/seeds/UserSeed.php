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

        for($x=0; $x < 6; $x++){
            $seed = [
                'mobile_number' => str_pad($faker->randomNumber(), 11, '09', STR_PAD_LEFT),
                'email' => $faker->safeEmail,
                'password' => password_hash('secret', PASSWORD_DEFAULT),
                'full_name' => $faker->name,
            ];

            User::create($seed);
        }
    }
}
