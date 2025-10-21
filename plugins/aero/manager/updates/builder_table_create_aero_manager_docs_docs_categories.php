<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerDocsDocsCategories extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_docs_docs_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('docs_id');
            $table->smallInteger('docs_categories_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_docs_docs_categories');
    }
}
