<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Messages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create table
        Schema::create('messages', function(Blueprint $table)
        {
            $table->increments('id');
            $table->enum('message_type', ['incoming', 'outgoing', 'blast', 'draft'])
                ->default('blast');
            $table->string('message', 160);
            $table->integer('to_user_id')->unsigned();
            $table->integer('from_user_id')->unsigned();
            $table->foreign('to_user_id')
                ->references('id')
                ->on('users');
            $table->foreign('from_user_id')
                ->references('id')
                ->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('messages');
    }
}
