<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create table
        Schema::create('users_documents', function(Blueprint $table) {
            $table->increments('id');
            $table->string('docu_type');
            $table->string('docu_title');
            $table->string('docu_uri')->nullable();
            $table->string('docu_desc')->nullable();
            $table->integer('user_id')
                ->unsigned();
            $table->string('docu_encrypt', 60);
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id', 'user_id', 'created_at']);
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
        Schema::drop('users_documents');
    }
}
