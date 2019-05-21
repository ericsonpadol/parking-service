<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscribersEventsChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create table
        Schema::create('subscribers_events_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->unsigned();
            $table->integer('channel_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->enum('status', ['sent', 'queued']);
            $table->timestamps();
            $table->foreign('event_id')
                ->references('id')
                ->on('core_events');
            $table->foreign('channel_id')
                ->references('channel_id')
                ->on('subscribers_channels');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('subscribers_channels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //drop schema
        Schema::drop('subscribers_events_channels');
    }
}
