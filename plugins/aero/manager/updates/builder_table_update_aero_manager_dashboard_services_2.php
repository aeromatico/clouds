<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardServices2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_dashboard_services', function($table)
        {
            $table->smallInteger('docs_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_dashboard_services', function($table)
        {
            $table->dropColumn('docs_id');
        });
    }
}
