<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_session', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('child_id')->length(11)->unsigned();
            $table->foreign('child_id')->references('id')->on('child');
            $table->string('sessions')->nullable();
            $table->datetime('server_time')->nullable();
            $table->datetime('local_time')->nullable();
            $table->integer('type')->nullable();
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
        
        Schema::dropIfExists('schedule_session');
    }
}
