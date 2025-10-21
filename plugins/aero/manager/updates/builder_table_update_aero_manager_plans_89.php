<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans89 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->boolean('domain_support');
            $table->string('domain')->nullable(false)->unsigned(false)->default('boliviahost.com')->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('domain_support');
            $table->boolean('domain')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
