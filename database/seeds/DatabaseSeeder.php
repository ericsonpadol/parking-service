<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Vehicle;
use App\ParkingSpace;
use App\Subscriber;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Vehicle::truncate();
        ParkingSpace::truncate();
        User::truncate();
        Model::unguard();

        $this->call('VehicleSeed');
        $this->call('UserSeed');
        $this->call('ParkingSpace');
    }
}
