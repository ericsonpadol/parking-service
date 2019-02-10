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
            $table->integer('transaction_id');
            $table->string('status');
            $table->string('action');
            $table->text('log');
            $table->integer('user_id');
            $table->timestamps();
            $table->index(['transaction_id', 'user_id']);
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
