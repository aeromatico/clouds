<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans117 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->string('promo_url');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('promo_url');
        });
    }
}
