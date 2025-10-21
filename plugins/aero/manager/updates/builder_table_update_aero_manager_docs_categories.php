<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDocsCategories extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_docs_categories', function($table)
        {
            $table->integer('parent_id');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_docs_categories', function($table)
        {
            $table->dropColumn('parent_id');
        });
    }
}
