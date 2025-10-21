<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardCardsLog2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_dashboard_cards_log', function($table)
        {
            $table->string('type');
            $table->decimal('amount', 10, 0);
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_dashboard_cards_log', function($table)
        {
            $table->dropColumn('type');
            $table->dropColumn('amount');
        });
    }
}
