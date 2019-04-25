<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MessagesStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages_status', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('message_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->enum('message_status', ['read', 'unread'])->default('unread');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('messages');
            $table->foreign('message_id')
                ->references('id')
                ->on('messages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('messages_status');
    }
}
