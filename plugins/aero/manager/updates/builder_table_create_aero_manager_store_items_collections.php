<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerStoreItemsCollections extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_store_items_collections', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('collection_id');
            $table->smallInteger('item_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_store_items_collections');
    }
}
