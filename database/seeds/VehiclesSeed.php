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
    public function run()
    {
       $faker = Faker::create();
        $seed = [
            'plate_number' => $faker->jpjNumberPlate;
        ];
        Vehicle::create();
    }
}
