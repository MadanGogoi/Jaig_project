<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryToReward extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reward', function ($table) {
          
             $table->integer('reward_type_id')->length(11)->default(0)->after('compliance');
              $table->string('image')->nullable()->after('compliance');
              $table->integer('compliance_reached')->length(11)->default(0)->after('compliance');
             
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reward', function($table) {
            $table->dropColumn('reward_type_id');
        });
    }
}
