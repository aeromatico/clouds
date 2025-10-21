<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStoreItems7 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->boolean('public_on')->nullable()->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->dropColumn('public_on');
        });
    }
}
