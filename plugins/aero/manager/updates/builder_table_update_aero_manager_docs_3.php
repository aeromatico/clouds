<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerDocs3 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_docs', function($table)
        {
            $table->dropColumn('domain');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_docs', function($table)
        {
            $table->string('domain', 191)->nullable();
        });
    }
}
