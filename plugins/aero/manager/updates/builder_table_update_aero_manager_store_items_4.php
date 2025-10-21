<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStoreItems4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->string('domain', 191)->default('store.clouds.com.bo')->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->string('domain', 191)->default(null)->change();
        });
    }
}
