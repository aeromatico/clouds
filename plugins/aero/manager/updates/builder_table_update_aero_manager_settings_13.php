<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerSettings13 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->renameColumn('sitemap_service_base', 'sitemap_services_base');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->renameColumn('sitemap_services_base', 'sitemap_service_base');
        });
    }
}
