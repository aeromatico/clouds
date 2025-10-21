<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerFeatures extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_features', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('feature');
            $table->text('description');
            $table->string('type');
            $table->smallInteger('service_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_features');
    }
}
