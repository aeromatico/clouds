<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDocsCategories2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_docs_categories', function($table)
        {
            $table->string('name', 191)->nullable()->change();
            $table->string('slug', 191)->nullable()->change();
            $table->integer('parent_id')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_docs_categories', function($table)
        {
            $table->string('name', 191)->nullable(false)->change();
            $table->string('slug', 191)->nullable(false)->change();
            $table->integer('parent_id')->nullable(false)->change();
        });
    }
}
