<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerServicesDocs extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_services_docs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('services_id');
            $table->smallInteger('docs_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_services_docs');
    }
}
