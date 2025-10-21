<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStoreCollections extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_store_collections', function($table)
        {
            $table->string('domain', 191)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_store_collections', function($table)
        {
            $table->string('domain', 191)->default('jumechi.store')->change();
        });
    }
}
