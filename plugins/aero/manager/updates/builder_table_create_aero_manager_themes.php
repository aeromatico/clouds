<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerThemes extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_themes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->string('category');
            $table->text('description');
            $table->smallInteger('img');
            $table->text('demo_link');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_themes');
    }
}
