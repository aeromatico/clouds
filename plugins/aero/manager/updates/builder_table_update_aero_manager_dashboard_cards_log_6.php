<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDashboardCardsLog6 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_dashboard_cards_log', function($table)
        {
            $table->decimal('amount', 10, 0)->nullable()->change();
            $table->string('detail', 191)->nullable()->change();
            $table->boolean('processed')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_dashboard_cards_log', function($table)
        {
            $table->decimal('amount', 10, 0)->nullable(false)->change();
            $table->string('detail', 191)->nullable(false)->change();
            $table->boolean('processed')->nullable(false)->change();
        });
    }
}
