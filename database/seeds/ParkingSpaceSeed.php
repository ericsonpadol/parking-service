<?php

use Illuminate\Database\Seeder;
use App\ParkingSpace;
use Faker\Factory as Faker;

class ParkingSpaceSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        for ($x = 0; $x < 100; ++$x) {
            $seed = [
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'zipcode' => $faker->postcode,
                'building_name' => $faker->buildingNumber,
                'space_lat' => $faker->latitude(14.000000, 14.999999),
                'space_lon' => $faker->longitude(121.000000, 121.999999),
                'establishment_type' => $faker->randomElement(['resident', 'commercial', 'public']),
                'description' => $faker->text($maxNbChars = 255),
                'parking_slot' => $faker->randomLetter.$faker->numberBetween(1, 200),
                'user_id' => ($faker->numberBetween(1, 5) * 10) + 1,
                'ratings' => $faker->numberBetween(1,5),
            ];

            ParkingSpace::create($seed);
        }
    }
}
