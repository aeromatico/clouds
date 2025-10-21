<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPortal38 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->string('accent_color', 7);
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->dropColumn('accent_color');
        });
    }
}
