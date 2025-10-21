<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDocsCategories3 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_docs_categories', function($table)
        {
            $table->boolean('parent_on')->default(0);
            $table->smallInteger('parent_id')->nullable()->unsigned(false)->default(0)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_docs_categories', function($table)
        {
            $table->dropColumn('parent_on');
            $table->integer('parent_id')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
