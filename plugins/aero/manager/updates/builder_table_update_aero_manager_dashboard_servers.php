<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardServers extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_dashboard_servers', function($table)
        {
            $table->dropColumn('finished_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_dashboard_servers', function($table)
        {
            $table->dateTime('finished_at')->nullable();
        });
    }
}
