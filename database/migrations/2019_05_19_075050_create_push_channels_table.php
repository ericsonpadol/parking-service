<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create table
        Schema::create('push_channels', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('channel_name');
            $table->enum('channel_type', ['public', 'private', 'presence']);
            $table->text('ch_desc');
            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
            $table->timestamps();
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
        Schema::drop('push_channels');
    }
}
