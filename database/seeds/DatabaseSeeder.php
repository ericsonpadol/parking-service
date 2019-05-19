<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Vehicle;
use App\ParkingSpace;
use App\TopUp;
use App\ParkingSpacePrice;
use App\AccountSecurity;
use App\PushChannel;

class DatabaseSeeder extends Seeder
{
    private $_accountSecurityTable = 'accountsecurity_user';
    private $_resetPasswordTable = 'reset_password';
    private $_messageTable = 'messages';
    private $_messageStatus = 'messages_status';
    private $_coreEventsTable = 'core_events';
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement('SET @@auto_increment_increment=10');
        Vehicle::truncate();
        User::truncate();
        ParkingSpace::truncate();
        TopUp::truncate();
        ParkingSpacePrice::truncate();
        AccountSecurity::truncate();
        PushChannel::truncate();
        DB::table($this->_accountSecurityTable)->truncate();
        DB::table($this->_resetPasswordTable)->truncate();
        DB::table($this->_messageTable)->truncate();
        DB::table($this->_messageStatus)->truncate();
        DB::table($this->_coreEventsTable)->truncate();
        Model::unguard();

        $this->call('UserSeed');
        $this->call('VehicleSeed');
        $this->call('TopUpSeed');
        $this->call('ParkingSpacePriceSeed');
        $this->call('ParkingSpaceSeed');
        $this->call('AccountSecuritySeed');
        $this->call('PushChannelSeed');
    }
}
