<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountryToSpacersessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spacersession', function ($table) {
          
            $table->string('spacer_id')->nullable()->after('notes');
            $table->string('spacer_string')->nullable()->after('spacer_id');
            $table->string('country')->nullable()->after('spacer_string');
            $table->string('zone')->nullable()->after('country');
             
           
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
            $table->dropColumn('spacer_id');
        });
    }
}
