<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswerSecquestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ref_answersecques', function (Blueprint $table) {
            $table->increments('id');
            $table->char('secques_id', 11);
            $table->integer('user_id');
            $table->string('answer_value', 60); //md5 hashed value
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
        Schema::drop('tbl_ref_answersecques');
    }
}
