<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignToChild extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('child', function ($table) {
            $table->integer('country_id')->length(11)->unsigned()->nullable()->after('profile_image');
            $table->foreign('country_id')->references('id')->on('country');           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('child', function($table) {
            
        });
    }
}
 