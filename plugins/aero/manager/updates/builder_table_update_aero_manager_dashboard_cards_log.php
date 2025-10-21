<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardCardsLog extends Migration
{
    public function up()
    {
        Schema::rename('aero_manager_dashboard_cards_activations', 'aero_manager_dashboard_cards_log');
    }
    
    public function down()
    {
        Schema::rename('aero_manager_dashboard_cards_log', 'aero_manager_dashboard_cards_activations');
    }
}
