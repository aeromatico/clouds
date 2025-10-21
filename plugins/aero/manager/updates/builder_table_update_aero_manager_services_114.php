<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices114 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->renameColumn('performance', 'server_performance');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->renameColumn('server_performance', 'performance');
        });
    }
}
