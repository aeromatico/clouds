<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDocs2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_docs', function($table)
        {
            $table->string('domain', 191)->nullable()->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_docs', function($table)
        {
            $table->string('domain', 191)->nullable(false)->default('boliviahost.com,llajwa.club,norte.host,difunde.cloud')->change();
        });
    }
}
