<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChildTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('child', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->date('dob')->nullable();
            $table->date('join_date')->nullable();
            $table->integer('user_id')->length(11)->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('gender', ['M', 'F'])->default('M');
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->string('spacer_id')->nullable();
            $table->string('country');
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
                
        Schema::dropIfExists('child');
    }
}
