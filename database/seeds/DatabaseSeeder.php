<?php

use Illuminate\Database\Seeder;

use App\Vehicle;
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
        Model::unguard();

        $this->call('VehicleSeed');
        $this->call('SubscriberSeed');
    }
}
