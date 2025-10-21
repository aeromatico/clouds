<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerPortalFeatures extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_portal_features', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('portal_id');
            $table->smallInteger('features_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_portal_features');
    }
}
