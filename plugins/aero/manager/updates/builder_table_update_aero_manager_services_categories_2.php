<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServicesCategories2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services_categories', function($table)
        {
            $table->string('domain')->nullable()->default('boliviahost.com');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services_categories', function($table)
        {
            $table->dropColumn('domain');
        });
    }
}
