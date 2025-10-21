<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStoreItems9 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->text('variants')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->dropColumn('variants');
        });
    }
}
