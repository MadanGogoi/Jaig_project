<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpacerdataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spacerdata', function (Blueprint $table) {
            $table->increments('id');
            $table->string('spacer_id')->nullable();
            $table->string('spacer_string')->nullable();
            $table->date('sync_date')->nullable();
            $table->date('datetime')->nullable();
            $table->string('timezone')->nullable();
            $table->string('country')->nullable();
            $table->integer('child_id')->length(11)->unsigned();
            $table->foreign('child_id')->references('id')->on('child');
            $table->string('technique')->nullable();
            $table->string('is_attack')->nullable();
            $table->integer('daily_session')->nullable();
            $table->integer('attack_session')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('spacerdata');
    }
}
