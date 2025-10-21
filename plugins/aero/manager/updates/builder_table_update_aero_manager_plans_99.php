<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans99 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->string('server_websites_number')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->smallInteger('server_websites_number')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
