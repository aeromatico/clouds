<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerContents extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_contents', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->string('subtitle');
            $table->string('slug');
            $table->text('content');
            $table->string('tags');
            $table->string('author');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_contents');
    }
}
