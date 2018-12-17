<?php

use Illuminate\Database\Seeder;

use App\Vehicle;
use Faker\Factory as Faker;

class VehicleSeed extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $faker = Faker::create();

        for($x=0; $x < 20; $x++){
            $seed = [
            'plate_number' => $this->randomizePlateNumber(),
            'color' => $faker->safeColorName,
            'model' => $faker->word,
            'brand' => $faker->word,
            'user_id' => ($faker->numberBetween(1, 5) * 10) + 1,
            ];

            Vehicle::create($seed);
        }
    }

    /**
     * Create a random plate number.
     */
    public function randomizePlateNumber() {
        $faker = Faker::create();
        $platenumber = [$faker->lexify('???'), $faker->numberBetween(1000, 9999)];
        $platenumber = implode($platenumber);

        return strtoupper($platenumber);
    }

}
