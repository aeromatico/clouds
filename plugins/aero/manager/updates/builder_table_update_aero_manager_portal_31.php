<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPortal31 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->smallInteger('header_theme')->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->dropColumn('header_theme');
        });
    }
}
