<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerServices extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_services', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->text('short_description');
            $table->text('description');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_services');
    }
}
