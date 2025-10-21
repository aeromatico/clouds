<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerSettings27 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->string('logo_text')->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->string('logo_text', 191)->change();
        });
    }
}
