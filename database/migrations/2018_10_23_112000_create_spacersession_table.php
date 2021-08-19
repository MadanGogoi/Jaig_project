<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpacersessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spacersession', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('child_id')->length(11)->unsigned();
            $table->foreign('child_id')->references('id')->on('child');
            $table->date('date')->nullable();
            $table->time('firsttime')->nullable();
            $table->time('lasttime')->nullable();
            $table->boolean('is_attack')->default(0);
            $table->integer('totalpasscount')->length(11)->nullable();
            $table->integer('totalfailcount')->length(11)->nullable();
            $table->integer('type')->length(11)->default(0);
            $table->datetime('local_time')->nullable();
            $table->string('notes')->nullable();
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
        
        Schema::dropIfExists('spacersession');
    }
}
