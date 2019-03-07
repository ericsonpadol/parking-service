<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Vehicle;
use App\ParkingSpace;
use App\TopUp;
use App\ParkingSpacePrice;
use App\AccountSecurity;

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
        TopUp::truncate();
        ParkingSpacePrice::truncate();
        AccountSecurity::truncate();
        Model::unguard();

        $this->call('UserSeed');
        $this->call('VehicleSeed');
        $this->call('TopUpSeed');
        $this->call('ParkingSpacePriceSeed');
        $this->call('ParkingSpaceSeed');
        $this->call('AccountSecuritySeed');
    }
}
