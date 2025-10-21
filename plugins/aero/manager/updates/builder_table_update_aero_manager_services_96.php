<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices96 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->smallInteger('footer_theme')->default(1);
            $table->smallInteger('theme')->default(1)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->dropColumn('footer_theme');
            $table->smallInteger('theme')->default(null)->change();
        });
    }
}
