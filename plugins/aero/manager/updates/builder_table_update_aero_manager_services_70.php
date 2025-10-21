<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices70 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->renameColumn('audio_on', 'cloud_on');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->renameColumn('cloud_on', 'audio_on');
        });
    }
}
