<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStoreItems8 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->dropColumn('variants');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->text('variants')->nullable();
        });
    }
}
