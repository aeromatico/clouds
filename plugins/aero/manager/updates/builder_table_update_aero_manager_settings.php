<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerSettings extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->increments('id')->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->integer('id')->change();
        });
    }
}
