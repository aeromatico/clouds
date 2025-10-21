<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerSettings5 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->string('link_messenger');
            $table->string('link_whatsapp');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->dropColumn('link_messenger');
            $table->dropColumn('link_whatsapp');
        });
    }
}
