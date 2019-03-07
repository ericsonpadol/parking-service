<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\ParkingSpacePrice;

class ParkingSpacePriceSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();
        $startDate = new DateTime('NOW');
        $endDate = new DateTime('NOW');

        for ($x = 0; $x < 10; ++$x) {
            $seed = [
                'pspace_price' => $faker->numberBetween(40, 100),
                'avail_start_datetime' => $startDate->modify('+1 day'),
                'avail_end_datetime' => $endDate->modify('+2 days'),
                'parking_space_id' => $faker->numberBetween(2, 21),
                'user_id' => ($faker->numberBetween(1, 5) * 10) + 1,
            ];

            ParkingSpacePrice::create($seed);
        }
    }
}
