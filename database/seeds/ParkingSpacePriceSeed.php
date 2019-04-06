<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\ParkingSpacePrice;
use App\TopUp;

class ParkingSpacePriceSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        //get TopUp values
        $topups = TopUp::all();
        $faker = Faker::create();
        $startDate = new DateTime('NOW');
        $endDate = new DateTime('NOW');

        for ($x = 0; $x < 50; ++$x) {
            $basePrice =  $faker->numberBetween(40, 100);
            $seed = [
                'pspace_base_price' => $basePrice,
                'pspace_calc_price' => round($basePrice + ($basePrice * $topups[0]->topup_value)
                    + ($basePrice * $topups[2]->topup_value)),
                'avail_start_datetime' => $startDate->modify('+1 day'),
                'avail_end_datetime' => $endDate->modify('+2 days'),
                'parking_space_id' => ($faker->numberBetween(1, 100) * 10) + 1,
                'user_id' => ($faker->numberBetween(1, 6) * 10) + 1,
            ];

            ParkingSpacePrice::create($seed);
        }
    }
}
