<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardServers2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_dashboard_servers', function($table)
        {
            $table->decimal('credits', 10, 0)->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_dashboard_servers', function($table)
        {
            $table->dropColumn('credits');
        });
    }
}
