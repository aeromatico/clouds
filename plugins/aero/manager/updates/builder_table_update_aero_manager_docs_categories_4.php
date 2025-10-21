<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDocsCategories4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_docs_categories', function($table)
        {
            $table->string('domain')->default('boliviahost.com,llajwa.club,norte.host,difunde.cloud');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_docs_categories', function($table)
        {
            $table->dropColumn('domain');
        });
    }
}
