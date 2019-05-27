<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Core Events - will log the event
 */
class CoreEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create table
        Schema::create('core_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_name');
            $table->string('event-subject');
            $table->text('event_text');
            $table->integer('user_id')->unsigned();
            $table->enum('event-type', ['booking', 'payment', 'blast', 'message', 'login', 'logout']);
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //drop table
        Schema::drop('core_events');
    }
}
