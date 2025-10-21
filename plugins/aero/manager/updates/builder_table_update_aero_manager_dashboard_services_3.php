<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardServices3 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_dashboard_services', function($table)
        {
            $table->text('ip')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_dashboard_services', function($table)
        {
            $table->dropColumn('ip');
        });
    }
}
