<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPortal34 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->smallInteger('home_theme');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->dropColumn('home_theme');
        });
    }
}
