<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerStorePruductos extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_store_pruductos', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->smallInteger('user_id');
            $table->string('domain');
            $table->text('description_short');
            $table->text('description');
            $table->text('variants');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_store_pruductos');
    }
}
