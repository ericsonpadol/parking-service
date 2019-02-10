<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Vehicle;
use App\ParkingSpace;
use App\TopUps;
use App\ParkingSpacePrice;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Vehicle::truncate();
        User::truncate();
        ParkingSpace::truncate();
        TopUps::truncate();
        ParkingSpacePrice::truncate();
        Model::unguard();

        $this->call('UserSeed');
        $this->call('VehicleSeed');
        $this->call('TopUps');
        $this->call('ParkingSpacePrice');
        $this->call('ParkingSpace');
    }
}
