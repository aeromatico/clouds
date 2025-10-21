<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStorePruductos extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_store_pruductos', function($table)
        {
            $table->text('pricing');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_store_pruductos', function($table)
        {
            $table->dropColumn('pricing');
        });
    }
}
