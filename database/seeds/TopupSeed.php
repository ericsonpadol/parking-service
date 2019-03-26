<?php

use Illuminate\Database\Seeder;
use App\TopUp;
use Faker\Factory as Faker;

class TopUpSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $faker = Faker::create();
        $key = ['topup', 'penalty', 'tax', 'cancellation_fee'];
        $value = ['0.30', '0.30', '0.12', '0.10'];
        for ($x = 0; $x < 4; $x++) {
            $seed = [
               'topup_key' => $key[$x],
               'topup_value' => $value[$x],
            ];

            TopUp::create($seed);
        }
    }
}
