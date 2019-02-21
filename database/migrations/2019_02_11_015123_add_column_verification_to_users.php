<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnVerificationToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //run
            $table->enum('is_activated', ['true', 'false'])
                ->default('false');
            $table->enum('is_lock', ['true', 'false'])
                ->default('false');
            $table->integer('is_lock_count')
                ->unsigned()
                ->default(0);
            $table->string('activation_token', 255);
            $table->time('lockout');
            $table->index(['email', 'mobile_number'], 'users_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //rollback
            $table->dropColumn('is_activated');
            $table->dropColumn('activation_token');
        });
    }
}
