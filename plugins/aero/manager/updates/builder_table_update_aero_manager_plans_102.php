<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans102 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->boolean('promo_black_friday');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('promo_black_friday');
        });
    }
}
