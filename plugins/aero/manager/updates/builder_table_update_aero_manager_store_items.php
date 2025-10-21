<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStoreItems extends Migration
{
    public function up()
    {
        Schema::rename('aero_manager_store_pruductos', 'aero_manager_store_items');
    }
    
    public function down()
    {
        Schema::rename('aero_manager_store_items', 'aero_manager_store_pruductos');
    }
}
