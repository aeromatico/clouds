<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerServicesServicesCategories extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_services_services_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('services_id');
            $table->smallInteger('categories_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_services_services_categories');
    }
}
