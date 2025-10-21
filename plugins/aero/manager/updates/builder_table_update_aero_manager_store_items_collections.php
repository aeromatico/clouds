<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStoreItemsCollections extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_store_items_collections', function($table)
        {
            $table->smallInteger('store_collection_id');
            $table->smallInteger('store_item_id');
            $table->dropColumn('collection_id');
            $table->dropColumn('item_id');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_store_items_collections', function($table)
        {
            $table->dropColumn('store_collection_id');
            $table->dropColumn('store_item_id');
            $table->smallInteger('collection_id');
            $table->smallInteger('item_id');
        });
    }
}
