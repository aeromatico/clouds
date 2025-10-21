<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerVisitors extends Migration
{
    public function up()
    {
        Schema::rename('aero_manager_stats', 'aero_manager_visitors');
    }
    
    public function down()
    {
        Schema::rename('aero_manager_visitors', 'aero_manager_stats');
    }
}
