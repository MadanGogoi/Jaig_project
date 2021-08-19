<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSessionnoToSpacersession extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spacersession', function ($table) {
          
              
            $table->string('session_no')->nullable()->after('zone');
            $table->double('session_tech',10,2)->nullable()->after('session_no');
               
             
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spacersession', function($table) {
            $table->dropColumn('session_tech');
            $table->dropColumn('session_no');
        });
    }
}
