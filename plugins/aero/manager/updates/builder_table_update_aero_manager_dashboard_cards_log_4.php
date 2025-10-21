<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardCardsLog4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_dashboard_cards_log', function($table)
        {
            $table->boolean('processed');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_dashboard_cards_log', function($table)
        {
            $table->dropColumn('processed');
        });
    }
}
