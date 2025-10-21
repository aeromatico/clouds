<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardLog extends Migration
{
    public function up()
    {
        Schema::rename('aero_manager_dashboard_cards_log', 'aero_manager_dashboard_log');
    }
    
    public function down()
    {
        Schema::rename('aero_manager_dashboard_log', 'aero_manager_dashboard_cards_log');
    }
}
