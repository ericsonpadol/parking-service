<?php

use Illuminate\Database\Seeder;
use App\ParkingSpace;
use Faker\Factory as Faker;

class ParkingSpaceSeed extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $faker = Faker::create();

        for ($x = 0; $x < 20; $x++) {
            $seed = [
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'zipcode' => $faker->postcode,
                'building_name' => $faker->buildingNumber,
                'space_lat' => $faker->latitude,
                'space_lon' => $faker->longitude,
                'establishment_type' => $faker->randomElement(['resident', 'commercial', 'public']),
                'description' => $faker->text($maxNbChars = 255),
                'user_id' => ($faker->numberBetween(1, 5) * 10) + 1,
            ];

            ParkingSpace::create($seed);
        }
    }

}
