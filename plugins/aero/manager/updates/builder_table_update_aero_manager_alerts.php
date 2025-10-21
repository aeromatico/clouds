<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerAlerts extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_alerts', function($table)
        {
            $table->string('provider');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_alerts', function($table)
        {
            $table->dropColumn('provider');
        });
    }
}
