<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateParkingAuditLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //run
        Schema::create('parking_audit_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('conv_id', 255);
            $table->string('status', 255);
            $table->string('action', 255);
            $table->text('log');
            $table->integer('user_id');
            $table->timestamp('created_at');
            $table->index(['conv_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //run
        Schema::drop('parking_audit_logs');
    }
}
