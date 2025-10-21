<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardCards extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_dashboard_cards', function($table)
        {
            $table->boolean('active');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_dashboard_cards', function($table)
        {
            $table->dropColumn('active');
        });
    }
}
