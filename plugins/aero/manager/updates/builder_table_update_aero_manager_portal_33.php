<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPortal33 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->string('header_class');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->dropColumn('header_class');
        });
    }
}
