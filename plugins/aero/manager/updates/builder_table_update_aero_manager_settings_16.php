<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerSettings16 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->text('whmcs_url');
            $table->text('whmcs_apikey');
            $table->text('whmc_secret');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->dropColumn('whmcs_url');
            $table->dropColumn('whmcs_apikey');
            $table->dropColumn('whmc_secret');
        });
    }
}
